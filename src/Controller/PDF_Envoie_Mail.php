<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Repository\SessionRepository;
use App\Repository\UtilisateurInstitutionSessionModuleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Service\PdfGenerateur;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

use Symfony\Component\Security\Http\Attribute\IsGranted;

class PDF_Envoie_Mail extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'limiter.pdf_generation_court')]
        private RateLimiterFactory $limiterCourt,

        #[Autowire(service: 'limiter.pdf_generation_moyen')]
        private RateLimiterFactory $limiterMoyen,

        #[Autowire(service: 'limiter.pdf_generation_long')]
        private RateLimiterFactory $limiterLong
    ) {}

    #[Route('/exporter/{id}/pdf', name: 'app_exporter_pdf', methods: ['POST'])]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function handlePdfRequest(
        Request $request,
        Utilisateur $utilisateur,
        UtilisateurInstitutionSessionModuleRepository $uisRepo,
        PdfGenerateur $pdfGenerateur,
        MailerInterface $mailer,
        EntityManagerInterface $em,
    ): Response {

            // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      


        $utilisateurConnecte = $this->getUser();
        $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateurConnecte);


        // SÉCURITÉ 1/9 - Vérification Surcharge serveur (Mémoire)
        // Récupérer la limite mémoire (ex: '128M', '2G', etc.)
        $limiteMemoire = ini_get('memory_limit');

        // Extraire la valeur numérique et convertir en octets
        $limiteMemoireEnBytes = (int)$limiteMemoire;
        if (stripos($limiteMemoire, 'M') !== false) {
            $limiteMemoireEnBytes *= 1024 * 1024; // Convertir en octets (si en M)
        } elseif (stripos($limiteMemoire, 'G') !== false) {
            $limiteMemoireEnBytes *= 1024 * 1024 * 1024; // Convertir en octets (si en G)
        }

        // Calculer le seuil de 80% de la mémoire
        $seuil = $limiteMemoireEnBytes * 0.8;

        // Vérification de l'utilisation mémoire
        $memoireUtilisee = memory_get_usage(true); // Utilisation mémoire actuelle

        // Si l'utilisation mémoire dépasse 80% de la limite
        if ($memoireUtilisee > $seuil) {
            throw new ServiceUnavailableHttpException(300, 'Mémoire serveur presque pleine - Opération reportée de 5min');
        }



        // SÉCURITÉ 2/9 - Rate Limiting renforcé
        $limitCle = sprintf(
            '%s_%s',
            $utilisateur->getId(),
            $request->getClientIp()
        );

        $limiter = $this->limiterCourt->create($limitCle);
        if (false === $limiter->consume()->isAccepted()) {

            $this->addFlash('error', 'Vous ne pouvez générer cette action qu\'une fois par minute.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }
        // SÉCURITÉ 3/9  Controle d'acces par role

        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_ENSEIGNANT')) {
            throw $this->createAccessDeniedException();
            $this->addFlash('error', 'Accès interdit. Vous devez être administrateur ou enseignant pour exporter des PDF.');
        }

        // SÉCURITÉ 4/9 - Validation du token CSRF
        if (!$this->isCsrfTokenValid('app_exporter_pdf', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }

        // SÉCURITÉ 5/9  Validation stricte des actions
        $action = $request->request->get('action', 'email');
        if ($action !== 'email' && $action !== 'telecharger') {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }

        // SÉCURITÉ 6/9 - pas besoin d'augmentation des ressources pour un seul envoie de pdf

        $utilisateurInstitutionSessionModules = $uisRepo->createQueryBuilder('uism')
            ->where('uism.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur) // Securité contre les injections SQL : setParameter() échappe automatiquement les valeurs
            ->getQuery()
            ->getResult();


        $html = $this->renderView('pdf/export.html.twig', [
            'utilisateur' => $utilisateur,
            'utilisateurInstitutionSessionModules' => $utilisateurInstitutionSessionModules,
            'pdf_mode' => true,
            'date' => new \DateTime(),
        ]);
        $pdfContenue = $pdfGenerateur->generationDepuisHTML($html);
        // SÉCURITÉ 7/9 -  Pas besoin de fichier temporaire téléchargement direct en mémoire sans fichiers temporaires

        // SÉCURITÉ 8/9 - Validation de la taille du PDF
        if (strlen($pdfContenue) > 1 * 1024 * 1024) { // 1Mo max par PDF
            throw new \RuntimeException('PDF trop volumineux');
        }
        if ($action === 'telecharger') {
            $fileName = sprintf(
                'releve_%s_%s.pdf',
                $utilisateur->getNom(),
                date('Y-m-d')
            );

            // SÉCURITÉ 9/9 - Pas besoin de Suppression automatique après téléchargement téléchargement direct en mémoire sans fichiers temporaires

            return new Response(
                $pdfContenue,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',  //force l'interprétation comme fichier PDF
                    'Content-Length' => strlen($pdfContenue), //Empêche les attaques (fichier corrompu si la taille annoncée ne correspond pas au contenu réel
                    'Cache-Control' => 'no-cache, no-store', //empêche le stockage non autorisé
                    'Content-Disposition' => 'attachment; filename="document.pdf"', //empêche l'ouverture du fichier sur le navigateur 
                    ]
            );
        } else {
            try {
                $email = (new TemplatedEmail())
                    ->from(new Address('muttalip.pro@gmail.com', 'Formatech'))
                    ->to($utilisateur->getCourriel())
                    ->subject('📄 Votre relevé de notes - ' . date('d/m/Y'))
                    ->htmlTemplate('model_emails/releve_notes_unique.html.twig')
                    ->context([
                        'utilisateur' => $utilisateur,
                        'date_envoi' => new \DateTime(),
                    ])
                    ->attach(
                        $pdfContenue,
                        sprintf('releve_notes_%s_%s_%s.pdf', $utilisateur->getNom(), $utilisateur->getPrenom(), date('Y-m-d')),
                        'application/pdf'
                    );

                $mailer->send($email);
                $this->addFlash('success', 'PDF envoyé avec succès à ' . $utilisateur->getCourriel());

                return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi du PDF : ' . $e->getMessage());
                return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
            }
        }
    }

    #[Route("/envoi-session-pdfs", name: 'envoi_session_pdfs', methods: ['POST'])]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function envoiSessionPdfs(
        Request $request,
        SessionRepository $sessionRepository,
        UtilisateurInstitutionSessionModuleRepository $uismRepo,
        PdfGenerateur $pdfGenerateur,
        MailerInterface $mailer,
        EntityManagerInterface $em,
    ): Response {

            // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      

        $utilisateurConnecte = $this->getUser();
        $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateurConnecte);

        $id = $utilisateur->getId();

        // SÉCURITÉ 1/9 - Vérification Surcharge serveur (Mémoire)
        // Récupérer la limite mémoire (ex: '128M', '2G', etc.)
        $limiteMemoire = ini_get('memory_limit');

        // Extraire la valeur numérique et convertir en octets
        $limiteMemoireEnBytes = (int)$limiteMemoire;
        if (stripos($limiteMemoire, 'M') !== false) {
            $limiteMemoireEnBytes *= 1024 * 1024; // Convertir en octets (si en M)
        } elseif (stripos($limiteMemoire, 'G') !== false) {
            $limiteMemoireEnBytes *= 1024 * 1024 * 1024; // Convertir en octets (si en G)
        }

        $seuil = $limiteMemoireEnBytes * 0.8;
        $memoireUtilisee = memory_get_usage(true);

        if ($memoireUtilisee > $seuil) {
            throw new ServiceUnavailableHttpException(300, 'Mémoire serveur presque pleine - Opération reportée de 5min');
        }

        // SÉCURITÉ 2/9 - Rate Limiting renforcé
        $limitCle = sprintf(
            '%s_%s',
            $utilisateur->getId(),
            $request->getClientIp()
        );

        $limiter = $this->limiterMoyen->create($limitCle);
        if (false === $limiter->consume()->isAccepted()) {

            $this->addFlash('error', 'Vous ne pouvez générer cette action qu\'une fois par heure.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $id]);
        }

        // SÉCURITÉ 3/9 - Contrôle d'accès renforcé
        if (!$this->isGranted('ROLE_ADMIN')  && !$this->isGranted('ROLE_ENSEIGNANT')) {
            $this->addFlash('error', 'Accès interdit. Vous devez être administrateur ou enseignant pour exporter des PDF de session.');
            throw $this->createAccessDeniedException();
        }

        // SÉCURITÉ 4/9 - Validation du token CSRF
        if (!$this->isCsrfTokenValid('envoi_session_pdfs', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $id]);
        }

        // SÉCURITÉ 5/9  Validation stricte des actions
        $action = $request->request->get('action', 'email');
        if ($action !== 'email' && $action !== 'telecharger') {
            return $this->redirectToRoute('utilisateur_liste', ['id' => $id]);
        }


        try {
            $sessionId = $request->request->get('session_id');
            $session = $sessionRepository->find((int)$sessionId);
            $action = $request->request->get('action');
            $sessionNom = $session->getNom(); // Supposant que votre entité a une méthode getNom()


            // SÉCURITÉ 6/9 - augmentation des ressoruces alouées pour pas avoir prblm limites de ressources 
            set_time_limit(360); // 6 minutes max pour traitement massif
            ini_set('memory_limit', '512M'); // Augmenté pour traitement massif

            $uismRecuperee = $uismRepo->createQueryBuilder('uism')
                ->join('uism.sessionModule', 'sm')
                ->join('uism.utilisateur', 'u')
                ->where('sm.session = :session')
                ->setParameter('session', $session) // Securité contre les injections SQL : setParameter() échappe automatiquement les valeurs
                ->getQuery()
                ->getResult();

            if (empty($uismRecuperee)) {
                $this->addFlash('warning', 'Aucun utilisateur trouvé pour cette session.');
                return $this->redirectToRoute('utilisateur_liste', ['id' => $id]);
            }

            $utilisateursEnEchec = [];
            $compteurReussite = 0;
            $zip = null;

            // SÉCURITÉ 7/9 - Stockage sécurisé des fichiers temporaires
            $zipNom = sys_get_temp_dir() . bin2hex(random_bytes(16)) . '.zip';

            if ($action === 'telecharger') {

                $zip = new \ZipArchive();
                if ($zip->open($zipNom, \ZipArchive::CREATE) !== TRUE) {
                    $this->addFlash('error', 'Impossible de créer l\'archive ZIP.');
                    return $this->redirectToRoute('utilisateur_liste', ['id' => $id]);
                }
            }

            foreach ($uismRecuperee as $uisEntity) {
                try {
                    $utilisateurCiblee = $uisEntity->getUtilisateur();

                    // Génération sécurisée du PDF
                    $html = $this->renderView('pdf/export.html.twig', [
                        'utilisateur' => $utilisateurCiblee,
                        'utilisateurInstitutionSessionModules' => $uismRepo->findBy(['utilisateur' => $utilisateurCiblee]),
                        'pdf_mode' => true,
                        'date' => new \DateTime(),
                        'session' => $session // Ajout de la session pour le contexte
                    ]);

                    $pdfContenue = $pdfGenerateur->generationDepuisHTML($html);

                    // SÉCURITÉ 8/9 - Validation de la taille du PDF
                    if (strlen($pdfContenue) > 1 * 1024 * 1024) { // 1Mo max par PDF
                        throw new \RuntimeException('PDF trop volumineux');
                    }

                    // Validation du contenu PDF généré
                    if (empty($pdfContenue)) {
                        $utilisateursEnEchec[] = $utilisateurCiblee->getId() . ' (PDF vide)';
                        continue;
                    }

                    if ($action === 'email') {
                        $email = (new TemplatedEmail())
                            ->from(new Address('muttalip.pro@gmail.com', 'Formatech'))
                            ->to($utilisateurCiblee->getCourriel())
                            ->subject('📄 Votre relevé de notes - Session ' . $sessionId)
                            ->htmlTemplate('model_emails/releve_notes_session.html.twig')
                            ->context([
                                'utilisateur' => $utilisateurCiblee,
                                'sessionId' => $sessionId,
                                'date_envoi' => new \DateTime(),
                            ])
                            ->attach(
                                $pdfContenue,
                                sprintf('releve_notes_%s_%s_%s.pdf', $sessionNom, $utilisateurCiblee->getNom(), $utilisateurCiblee->getPrenom()),
                                'application/pdf'
                            );


                        $mailer->send($email);
                        $this->addFlash('success', 'PDF envoyé avec succès à ' . $utilisateurCiblee->getCourriel());
                        $compteurReussite++;
                    } else if ($action === 'telecharger') {
                        $fileName = sprintf('releve_user_%s.pdf', $utilisateurCiblee->getNom());
                        $zip->addFromString($fileName, $pdfContenue);
                        $compteurReussite++;
                    }
                } catch (\RuntimeException $e) {
                    $utilisateursEnEchec[] = ($utilisateurCiblee?->getId() ?? 'inconnu') . ' (' . $e->getMessage() . ')';
                } catch (\Exception $e) {
                    $utilisateursEnEchec[] = ($utilisateurCiblee?->getId() ?? 'inconnu') . ' (erreur système)';
                }
            }

            if ($action === 'email') {
                // Messages de résultat pour l'envoi par email
                if ($compteurReussite > 0) {
                    $this->addFlash('success', sprintf('PDFs envoyés avec succès : %d/%d', $compteurReussite, count($uismRecuperee)));
                }

                if (!empty($utilisateursEnEchec)) {
                    $this->addFlash('warning', 'Échecs d\'envoi : ' . implode(', ', array_slice($utilisateursEnEchec, 0, 5)));
                }
            } else if ($action === 'telecharger') {
                if ($zip) {
                    $zip->close();

                    if ($compteurReussite > 0) {
                        $reponse = new BinaryFileResponse($zipNom);
                        $reponse->setContentDisposition(
                            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                            sprintf(
                                'export_session_%s_%s.zip',
                                ($session->getNom()),
                                date('Y-m-d')
                            )
                        );
                        // SÉCURITÉ 9/9 - Suppression automatique du fichier après téléchargement pour éviter les fuites
                        $reponse->deleteFileAfterSend(true);
                        $this->addFlash('success', sprintf('Fichiers ZIP générés avec succès : %d/%d', $compteurReussite, count($uismRecuperee)));
                        return $reponse;
                    }
                }
            }
        } catch (\Exception $e) {

            $this->addFlash('error', 'Erreur lors de l\'envoi des PDFs : ' . $e->getMessage());
            // Nettoyage en cas d'erreur
            if (isset($zipNom) && file_exists($zipNom)) {
                unlink($zipNom);
            }
        }
        return $this->redirectToRoute('utilisateur_liste', ['id' => $id]);
    }


    #[Route("/envoi-tout-pdfs", name: 'envoi_tout_pdfs', methods: ['POST'])]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function envoiToutPdfs(
        Request $request,
        UtilisateurRepository $utilisateurRepo,
        UtilisateurInstitutionSessionModuleRepository $uisRepo,
        PdfGenerateur $pdfGenerateur,
        MailerInterface $mailer,
        EntityManagerInterface $em,
    ): Response {

            // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      

        $utilisateurConnecte = $this->getUser();
        $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateurConnecte);



        // SÉCURITÉ 1/9 - Vérification Surcharge serveur (Mémoire)
        $limiteMemoire = ini_get('memory_limit');
        // Extraire la valeur numérique et convertir en octets
        $limiteMemoireEnBytes = (int)$limiteMemoire;
        if (stripos($limiteMemoire, 'M') !== false) {
            $limiteMemoireEnBytes *= 1024 * 1024; // Convertir en octets (si en M)
        } elseif (stripos($limiteMemoire, 'G') !== false) {
            $limiteMemoireEnBytes *= 1024 * 1024 * 1024; // Convertir en octets (si en G)
        }

        // Calculer le seuil de 80% de la mémoire
        $seuil = $limiteMemoireEnBytes * 0.8;
        $memoireUtilisee = memory_get_usage(true);

        if ($memoireUtilisee > $seuil) {
            throw new ServiceUnavailableHttpException(300, 'Mémoire serveur presque pleine - Opération reportée de 5min');
        }

        // SÉCURITÉ 2/9 - Rate Limiting renforcé
        $limitCle = sprintf('%s_%s', $utilisateur->getId(), $request->getClientIp());
        $limiter = $this->limiterLong->create($limitCle);

        if (false === $limiter->consume()->isAccepted()) {
            $this->addFlash('error', 'Vous ne pouvez générer cette action qu\'une fois par jour.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }

        // SÉCURITÉ 3/9 - Contrôle d'accès renforcé
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_ENSEIGNANT')) {
            $this->addFlash('error', 'Accès interdit. Vous devez être administrateur ou enseignant.');
            throw $this->createAccessDeniedException();
        }




        // SÉCURITÉ 4/9 - Protection CSRF
        if (!$this->isCsrfTokenValid('envoi_tout_pdfs', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }

        // SÉCURITÉ 5/9 - Validation stricte des actions
        $action = $request->request->get('action', 'email');
        if (!in_array($action, ['email', 'telecharger'], true)) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }

        // SÉCURITÉ 6/9 - Augmentation des ressources toutouées
        set_time_limit(180);
        ini_set('memory_limit', '256M');

        // Récupération des utilisateurs
        $utilisateurRecuperee = $utilisateurRepo->createQueryBuilder('u')
            ->select('u.id')
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($utilisateurRecuperee)) {
            $this->addFlash('warning', 'Aucun utilisateur trouvé.');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }


        // SÉCURITÉ 7/9 - Stockage temporaire sécurisé
        $zipNom = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(16)) . '.zip';

        $compteurReussite = 0;
        $compteurErreurs = 0;
        $utilisateursEnEchec = [];

        try {
            if ($action === 'telecharger') {
                // Traitement pour téléchargement ZIP
                $zip = new \ZipArchive();
                if ($zip->open($zipNom, \ZipArchive::CREATE) !== true) {
                    throw new \RuntimeException('Impossible de créer l\'archive ZIP');
                }

                foreach ($utilisateurRecuperee as $utilisateur_) {
                    try {
                        $user = $utilisateurRepo->find($utilisateur_);
                        if (!$user) {
                            continue;
                        }

                        $pdfContenue = $this->generateurPdfUtilisateur($user, $uisRepo, $pdfGenerateur);

                        // SÉCURITÉ 8/9 - Validation de la taille du PDF
                        if (strlen($pdfContenue) > 1 * 1024 * 1024) { // 1Mo max par PDF
                            throw new \RuntimeException('PDF trop volumineux');
                        }

                        $zip->addFromString(
                            sprintf('releve_%s_%s.pdf', $user->getNom(), date('Y-m-d')),
                            $pdfContenue
                        );
                        $compteurReussite++;
                    } catch (\Exception $e) {
                        $compteurErreurs++;
                        $utilisateursEnEchec[] = $user->getCourriel() ?? "Utilisateur {$utilisateur_}";


                        // Arrêt si trop d'erreurs
                        if ($compteurErreurs > 10) {
                            $this->addFlash('error', 'Trop d\'erreurs lors de la génération, arrêt du processus');
                            break;
                        }
                    }
                }
                $zip->close();

                if ($compteurReussite > 0) {
                    $reponse = new BinaryFileResponse($zipNom);
                    $reponse->setContentDisposition(
                        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                        sprintf('export_tout_%s.zip', date('Y-m-d_H-i-s'))
                    );
                    $reponse->deleteFileAfterSend(true);
                    return $reponse;
                } else {
                    if (file_exists($zipNom)) {
                        unlink($zipNom);
                    }
                    $this->addFlash('error', 'Aucun PDF n\'a pu être généré.');
                }
            } elseif ($action === 'email') {
                // Traitement pour envoi par email
                foreach ($utilisateurRecuperee as $utilisateur_) {
                    try {
                        $user = $utilisateurRepo->find($utilisateur_);
                        if (!$user || !$user->getCourriel()) {
                            continue;
                        }

                        $pdfContenue = $this->generateurPdfUtilisateur($user, $uisRepo, $pdfGenerateur);

                        //                         // SÉCURITÉ 8/9 -Validation de la taille du PDF
                        if (strlen($pdfContenue) > 1 * 1024 * 1024) {
                            throw new \RuntimeException('PDF trop volumineux');
                        }

                        $email = (new TemplatedEmail())
                            ->from(new Address('muttalip.pro@gmail.com', 'Formatech'))
                            ->to($user->getCourriel())
                            ->subject('📄 Votre relevé de notes')
                            ->htmlTemplate('model_emails/releve_notes_total.html.twig')
                            ->context([
                                'utilisateur' => $user,
                                'date_envoi' => new \DateTime(),
                            ])
                            ->attach(
                                $pdfContenue,
                                sprintf('releve_notes_%s_%s_%s.pdf', $user->getNom(), $user->getPrenom(), date('Y-m-d')),
                                'application/pdf'
                            );
                        $mailer->send($email);

                        $compteurReussite++;
                    } catch (\Exception $e) {
                        $compteurErreurs++;
                        $utilisateursEnEchec[] = $user->getCourriel() ?? "Utilisateur {$utilisateur_}";



                        if ($compteurErreurs > 10) {
                            $this->addFlash('error', 'Trop d\'erreurs lors de la génération, arrêt du processus');
                            break;
                        }
                    }
                }

                // Messages de résultat pour l'envoi par email
                if ($compteurReussite > 0) {
                    $this->addFlash('success', sprintf('PDFs envoyés avec succès : %d/%d', $compteurReussite, count($utilisateurRecuperee)));
                }
                if (!empty($utilisateursEnEchec)) {
                    $this->addFlash('warning', 'Échecs d\'envoi : ' . implode(', ', array_slice($utilisateursEnEchec, 0, 5)));
                }
            }
        } catch (\Exception $e) {
            // Nettoyage en cas d'erreur
            if (file_exists($zipNom)) {
                unlink($zipNom);
            }
            $this->addFlash('error', 'Erreur lors du traitement de la demande.');
        }

        return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
    }
    private function generateurPdfUtilisateur(
        $user,
        UtilisateurInstitutionSessionModuleRepository $uisRepo,
        PdfGenerateur $pdfGenerateur
    ): string {
        $html = $this->renderView('pdf/export.html.twig', [
            'utilisateur' => $user,
            'utilisateurInstitutionSessionModules' => $uisRepo->findBy(['utilisateur' => $user]),
            'pdf_mode' => true,
            'date' => new \DateTime(),
        ]);
        return $pdfGenerateur->generationDepuisHTML($html);
    }
}