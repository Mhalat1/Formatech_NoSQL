<?php

namespace App\Controller;

use App\Entity\FormContact;
use App\Entity\Utilisateur;
use App\Form\FormContactType;
use App\Repository\InstitutionRepository;
use App\Repository\ModuleRepository;
use App\Repository\SessionRepository;
use App\Repository\FormContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AccueilController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'limiter.pdf_generation_court')]
        private RateLimiterFactory $limiteCourte,
    ) {}

    #[Route('/', name: 'app_accueil')]
    public function index(
        Request $request,
        InstitutionRepository $institutionRepository,
        SessionRepository $sessionRepository,
        ModuleRepository $moduleRepository,
        FormContactRepository $formContactRepository,
        MailerInterface $mailer,
        //Logger retourne les message d'erreure
        LoggerInterface $logger,
        EntityManagerInterface $em


    ): Response {

    $utilisateur_session = $this->getUser();
    $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateur_session);

    $dateFin = $utilisateur ->getDateFinAbonnement(); 

        //dd($dateFin);
    // SÉCURITÉ 1/9 - Vérification Surcharge serveur (Mémoire)

        $limiteMemoire = ini_get('memory_limit');
        $usageMemoire = memory_get_usage(true);
        // Extraire la valeur numérique et convertir en octets
        $limiteMemoireBytes = (int)$limiteMemoire;
        if (stripos($limiteMemoire, 'M') !== false) {
            $limiteMemoireBytes *= 1024 * 1024; // Convertir en octets (si en M)
        } elseif (stripos($limiteMemoire, 'G') !== false) {
            $limiteMemoireBytes *= 1024 * 1024 * 1024; // Convertir en octets (si en G)
        }

        // Calculer le seuil de 80% de la mémoire
        $seuil = $limiteMemoireBytes * 0.8;

        if ($usageMemoire > $seuil) {
            throw new ServiceUnavailableHttpException(360, 'Mémoire serveur presque pleine - Opération reportée de 6min');
        }

        // 2. Récupération des données pour la page
        $institutions = $institutionRepository->findAll();
        $sessions = $sessionRepository->findAll();
        $modules = $moduleRepository->findAll();
        $contacts = $formContactRepository->findAll();


        // 4. Création et traitement du formulaire de contact
        $formContact = new FormContact();
        $form = $this->createForm(FormContactType::class, $formContact);
        $form->handleRequest($request);

        // SÉCURITÉ 4/9 - Validation du token CSRF
        if ($form->isSubmitted() && $form->isValid()) {

        // SÉCURITÉ 5/9  Validation stricte des actions
            $action = $request->request->get('action');
            if ($action !== 'email') {
                $logger->warning('Action invalide dans le formulaire de contact', [
                    'action' => $action,
                    'ip' => $request->getClientIp(),
                    'timestamp' => (new \DateTimeImmutable())->format('c')
                ]);
                $this->addFlash('error', 'Action non autorisée.');
                return $this->redirectToRoute('app_accueil');
            }
            

        // SÉCURITÉ 2/9 - Rate Limiting renforcé
            $limiteur = $this->limiteCourte->create($request->getClientIp());
            if (!$limiteur->consume()->isAccepted()) {
                $logger->info('limit seuil atteint pour le formulaire de contact', [
                    'ip' => $request->getClientIp(),
                    'timestamp' => (new \DateTimeImmutable())->format('c')
                ]);
                $this->addFlash('error', 'Trop de soumissions. Veuillez patienter une minute.');
                return $this->redirectToRoute('app_accueil');
            }
                $this->envoiEmail($mailer, $formContact);
                $this->addFlash('success', 'Votre message a bien été envoyé !');
                return $this->redirectToRoute('app_accueil');
            
        }


        return $this->render('Pages_principaux/page_accueil.html.twig', [
            'institutions' => $institutions,
            'sessions' => $sessions,
            'modules' => $modules,
            'contacts' => $contacts,
            'form' => $form->createView(),
            'dateFin' => $dateFin
        ]);
    }


    private function envoiEmail(MailerInterface $mailer, FormContact $formContact): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('muttalip.pro@gmail.com', 'Formatech'))
            ->to('halat@outlook.fr')
            ->subject('📬 Nouveau contact client')
            ->htmlTemplate('model_emails/nouveau_contact.html.twig')
            ->context([
                'contact' => $formContact,
                'date' => new \DateTime(),
            ]);

        $mailer->send($email);
    }
}