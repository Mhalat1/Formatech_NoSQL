<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Invitation;
use App\Entity\Utilisateur;
use App\Form\InvitationType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class InvitationController extends AbstractController
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private const MAX_INVITATIONS = 100;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    #[Route('/admin/invite', name: 'app_invite')]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function invite(Request $request, EntityManagerInterface $em): Response
    {

    // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $institution = $user->getInstitution();
        
        if (!$institution) {
            $this->addFlash('error', 'Vous n\'administrez aucune institution');
            return $this->redirectToRoute('app_accueil');
        }

        $comptageInvitations = $user->compteurInvitations();

        if ($comptageInvitations >= self::MAX_INVITATIONS) {
            $this->addFlash('error', sprintf('Limite de %d invitations atteinte', self::MAX_INVITATIONS));
        }
        
        $invitation = new Invitation();
        $form = $this->createForm(InvitationType::class, $invitation, [
            'utilisateur_connecté' => $user
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

                $utilisateur = $em->getRepository(Utilisateur::class)->find($user->getId());
                $utilisateur->incrementNombreInvitations();
                $em->persist($utilisateur);

                

                $invitation
                    ->setExpirele(new \DateTimeImmutable('+7 days'))
                    ->setCreeLe(new \DateTimeImmutable())
                    ->setInvitedBy($user)
                    ->setInstitution($institution);
                
                $em->persist($invitation);
                $em->flush();



                $this->envoyerInvitationMail($invitation);
                $this->addFlash('success', 'Invitation envoyée avec succès');
                return $this->redirectToRoute('app_invite');
        }
        return $this->render('invitation/nouvelle_invitation.html.twig', [
            'form' => $form->createView(),
            'comptageInvitations' => $comptageInvitations,
            'invitationsRestantes' => self::MAX_INVITATIONS - $comptageInvitations,
        ]);
    }

    private function envoyerInvitationMail(Invitation $invitation): void
    {
        try {
            $email = (new Email())
                ->from('no-reply@votre-domaine.com')
                ->to($invitation->getEmail())
                ->subject('Invitation à rejoindre notre plateforme')
                ->html($this->renderView('model_emails/invitation.html.twig', [
                    'invitation' => $invitation,
                    'expireLe' => $invitation->getExpirele()->format('Y-m-d H:i:s'),
                    'institution' => $invitation->getInstitution(),
                ]));

            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Erreur envoi email: '.$e->getMessage());
            throw $e;
        }
    }

    #[Route('/invitation/accept/{token}', name: 'app_invitation_accept')]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function acceptInvitation(string $token, EntityManagerInterface $em, Request $request): Response
    {

    // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      

        $invitationVerif = $em->getRepository(Invitation::class)->findOneBy(['token' => $token]);
        
        if (!$invitationVerif) {
            $this->addFlash('error', 'Token d\'invitation non trouvé');
            return $this->redirectToRoute('app_accueil');
        }
        
        // Debug: Vérifier si elle est expirée
        if ($invitationVerif->isExpired()) {
            $this->addFlash('error', sprintf(
                'Invitation expirée le %s', 
                $invitationVerif->getExpirele()->format('d/m/Y H:i')
            ));
            return $this->redirectToRoute('app_accueil');
        }
        
        // L'invitation est valide
        $invitation = $invitationVerif;
        
        if ($this->getUser()) {
            return $this->gererInvitation($invitation, $em);
        }
        
        $request->getSession()->set('invitation_token', $token);
        return $this->redirectToRoute('app_inscription', [
            'email' => $invitation->getEmail(),
            'institution' => $invitation->getInstitution()->getId()
        ]);
    }
    
    private function gererInvitation(Invitation $invitation, EntityManagerInterface $em): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if ($user->getInstitution()) {
            $this->addFlash('warning', 'Vous appartenez déjà à une institution');
            return $this->redirectToRoute('app_accueil');
        }

        try {
            $user->setInstitution($invitation->getInstitution());
            
            $em->remove($invitation);
            $em->flush();
            
            $this->addFlash('success', 'Vous avez rejoint l\'institution avec succès');
            return $this->redirectToRoute('app_accueil');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du traitement de l\'invitation');
            return $this->redirectToRoute('app_accueil');
        }
    }
}