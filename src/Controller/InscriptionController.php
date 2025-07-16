<?php

namespace App\Controller;
use App\Security\Authentification;


use App\Entity\Utilisateur;
use App\Entity\Institution;
use App\Entity\Invitation;
use App\Form\InscriptionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $utilisateurPasswordEncoder,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        $utilisateur = new Utilisateur();
        $formulaireinscription = $this->createForm(InscriptionFormType::class, $utilisateur);

        // Gestion de l'invitation
        $token = $request->query->get('token') ?? $request->getSession()->get('invitation_token');
        $invitationData = $request->getSession()->get('invitation_institution');
        $invitation = null;
        
        if ($token) {
            $invitation = $entityManager->getRepository(Invitation::class)->findValidInvitation($token);
            
            if ($invitation) {
                $utilisateur->setCourriel($invitation->getEmail());
                
                $institutionNom = $invitationData['nom'] ?? $invitation->getInstitution()->getNom();
                $institutionAdresse = $invitationData['adresse'] ?? $invitation->getInstitution()->getAdresse();
                $institutionTelephone = $invitationData['telephone'] ?? $invitation->getInstitution()->getTelephone();
                $institutionCourriel = $invitationData['courriel'] ?? $invitation->getInstitution()->getCourriel();
                
                $formulaireinscription->get('institutionNom')->setData($institutionNom);
                $formulaireinscription->get('institutionAdresse')->setData($institutionAdresse);
                $formulaireinscription->get('institutionTelephone')->setData($institutionTelephone);
                $formulaireinscription->get('institutionCourriel')->setData($institutionCourriel);
            }
        }

        $formulaireinscription->handleRequest($request);

        if ($formulaireinscription->isSubmitted() && $formulaireinscription->isValid()) {

                // Définir la date de fin d'abonnement (3 mois plus tard)
                $dateFinAbonnement = new \DateTime('+3 month');
                $utilisateur->setDateFinAbonnement($dateFinAbonnement);

            if ($token) {
                $utilisateur->setRoles(['ROLE_USER']);
            } else {
                $utilisateurCount = $entityManager->getRepository(Utilisateur::class)->count([]);
                $utilisateur->setRoles($utilisateurCount === 0 ? ['ROLE_ADMIN', 'ROLE_USER'] : ['ROLE_USER']);
            }

            if ($invitation) {
                $institution = $invitation->getInstitution();
                $utilisateur->setInstitution($institution);
                $entityManager->remove($invitation);
                
                $request->getSession()->remove('invitation_token');
                $request->getSession()->remove('invitation_institution');
            } else {
                $institutionNom = $formulaireinscription->get('institutionNom')->getData();
                $institutionAdresse = $formulaireinscription->get('institutionAdresse')->getData();
                $institutionTelephone = $formulaireinscription->get('institutionTelephone')->getData();
                $institutionCourriel = $formulaireinscription->get('institutionCourriel')->getData();
                
                $institution = $entityManager->getRepository(Institution::class)
                    ->findOneBy(['nom' => $institutionNom]);
                
                if (!$institution) {
                    $institution = new Institution();
                    $institution->setNom($institutionNom)
                        ->setAdresse($institutionAdresse)
                        ->setTelephone($institutionTelephone)
                        ->setCourriel($institutionCourriel);
                    $entityManager->persist($institution);
                }
                
                $utilisateur->setInstitution($institution);
            }

            $utilisateur->setPassword(
                $utilisateurPasswordEncoder->hashPassword(
                    $utilisateur,
                    $formulaireinscription->get('motdepasse')->getData()
                )
            );

            $entityManager->persist($utilisateur);
            $entityManager->flush();

            return $security->login($utilisateur, Authentification::class, 'main');
        }

        return $this->render('inscription/inscription.html.twig', [
            'formulaireInscription' => $formulaireinscription->createView(),
            'isInvitation' => $invitation !== null,
            'invitation' => $invitation,
            'invitationData' => $invitationData
        ]);
    }
}