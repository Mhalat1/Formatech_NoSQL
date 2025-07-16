<?php

namespace App\Controller;

use App\Repository\InstitutionRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\ModuleRepository;
use App\Repository\SessionRepository;
use App\Repository\UtilisateurInstitutionSessionModuleRepository;  
use App\Entity\Utilisateur;  
use App\Entity\Institution; 
use App\Entity\Session; 
use App\Entity\Module;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\InstitutionType;
use App\Form\ModuleType;
use App\Form\SessionType; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Form\UtilisateurRoleType; 
use App\Form\CommentaireModuleType;
use App\Form\UtilisateurCommentaireType;


use App\PartieReutilisable\DonneesCommunes;

use Doctrine\Persistence\ManagerRegistry;

class InstitutionController extends AbstractController
{
    use DonneesCommunes;

    #[Route('/infoinstitution', name: 'institution_index_ajouter')]
    #[IsGranted('ABONNEMENT_ACTIF')]

    public function indexEtAjouter(
        Request $request,
        InstitutionRepository $institutionRepository,
        UtilisateurRepository $utilisateurRepository,
        SessionRepository $sessionRepository,
        UtilisateurInstitutionSessionModuleRepository $utilisateurInstitutionSessionModuleRepository,
        ModuleRepository $moduleRepository,  
        EntityManagerInterface $em,
        ManagerRegistry $doctrine 

    ): Response {


      

        $donneesCommunes = $this->getDonneesCommunes($doctrine);
        $utilisateurInstitutionSessionModule = $utilisateurInstitutionSessionModuleRepository;
        $institutions = $institutionRepository->findAll();
        $sessions = $sessionRepository->findAll();
        $modules = $moduleRepository->findAll();
        $utilisateurs_liste = $utilisateurRepository->findAll();



        // PARTIE CREATIONS //

        //nouvelle session//
        $session = new Session();
        $form_session = $this->createForm(SessionType::class, $session, [
            'data_class' => Session::class,  
        ]);
        $form_session->handleRequest($request);
        // securité 1/1 verifie token car form symfony 
        if ($form_session->isSubmitted() && $form_session->isValid()) {
            $em->persist($session);
            $em->flush();
            $this->addFlash('success', 'Session ajoutée avec succès!');
            return $this->redirectToRoute('institution_index_ajouter');
        }

        // Créer un nouvel objet Module
        $module = new Module();
        $form_module = $this->createForm(ModuleType::class, $module,  [
        // data_class est une variable symfony qui récupére toutes les variables du formulaire
            'data_class' => Module::class, 
        ]);
        $form_module->handleRequest($request);

        if ($form_module->isSubmitted() && $form_module->isValid()) {
            $em->persist($module);
            $em->flush();
            $this->addFlash('success', 'Module ajouté avec succès!');
            return $this->redirectToRoute('institution_index_ajouter');
        }


        // créer nouvelle institution
        $institution = new Institution();
        $form_institution = $this->createForm(InstitutionType::class, $institution);
        $form_institution->handleRequest($request);
        if ($form_institution->isSubmitted() && $form_institution->isValid()) {
            $em->persist($institution);
            $em->flush();
            $this->addFlash('success', 'Institution ajoutée avec succès!');
            return $this->redirectToRoute('institution_index_ajouter');
        }


        // PARTIE SUPPRESIONS //

        // supprime institution
        $supprimerInstitution = $request->get('supprimer_Institution');
        if ($supprimerInstitution) {
            $institution_a_supprimer = $institutionRepository->find($supprimerInstitution);

            if ($institution_a_supprimer) {
                $em->remove($institution_a_supprimer);
                $em->flush();
                $this->addFlash('success', 'Institution supprimée avec succès!');
            } else {
                $this->addFlash('error', 'Institution non trouvée!');
            }
            return $this->redirectToRoute('institution_index_ajouter');
        }


        // Gérer la suppression d'un module
        $supprimerModule = $request->get('supprimer_Module');
        if ($supprimerModule) {
            $module_a_supprimer = $moduleRepository->find($supprimerModule);

            if ($module_a_supprimer) {
                $em->remove($module_a_supprimer);
                $em->flush();
                $this->addFlash('success', 'Module supprimé avec succès!');
            } else {
                $this->addFlash('error', 'Module non trouvé!');
            }
            return $this->redirectToRoute('institution_index_ajouter');
        }


        // supprime session
        $supprimerSession = $request->get('supprimer_Session');
        if ($supprimerSession) {
            $session_a_supprimer = $sessionRepository->find($supprimerSession);

            if ($session_a_supprimer) {
                $em->remove($session_a_supprimer);
                $em->flush();
                $this->addFlash('success', 'Session supprimée avec succès!');
            } else {
                $this->addFlash('error', 'Session non trouvée!');
            }
            return $this->redirectToRoute('institution_index_ajouter');
        }


        // Gérer la suppression d'un utilisateur
        $supprimerUtilisateur = $request->get('supprimer_utilisateur');
        if ($supprimerUtilisateur) {
            $Utilisateur_a_supprimer = $utilisateurRepository->find($supprimerUtilisateur);

            if ($Utilisateur_a_supprimer) {
                $em->remove($Utilisateur_a_supprimer);
                $em->flush();
                $this->addFlash('success', 'Utilisateur supprimé avec succès!');
            } else {
                $this->addFlash('error', 'Utilisateur non trouvé!');
            }

            return $this->redirectToRoute('institution_index_ajouter');
        }


        return $this->render('Pages_principaux/page_utilisateur.html.twig', [
            'utilisateurs_liste' => $utilisateurs_liste,
            'institutions' => $institutions,
            'sessions' => $sessions,
            'modules' => $modules,
            'form_institution' => $form_institution->createView(),
            'form_session' => $form_session->createView(),
            'form_module' => $form_module->createView(),
            'utilisateurInstitutionSessionModule' => $utilisateurInstitutionSessionModule,
            ...$donneesCommunes,
        ],);
    }



    #[Route("/infoutilisateur/{id}/liste", name: 'utilisateur_liste', methods: ['GET'])]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function liste(
        Utilisateur $utilisateur,
        UtilisateurInstitutionSessionModuleRepository $uisRepo,
        ManagerRegistry $doctrine
    ): Response {

                    // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      

        $uism = $uisRepo->findBy(['utilisateur' => $utilisateur]);
        $sessions = [];

        foreach ($uism as $entity) {
            $sessionModule = $entity->getSessionModule();
            if ($sessionModule && $session = $sessionModule->getSession()) {
                $session->__load(); // pour ne pas avoir un proxy vide
                $sessions[] = $session;
            }
        }

        return $this->render('Pages_principaux/page_utilisateur_info.html.twig', [
            'utilisateur' => $utilisateur,
            'utilisateurInstitutionSessionModules' => $uism,
            'session_liee' => $sessions,
            ...$this->getDonneesCommunes($doctrine),
        ]);
    }




    #[Route("/utilisateur/{id}/modifier-roles", name: 'utilisateur_modifier_roles')]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function modifierRoles(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $em,
        ManagerRegistry $doctrine // _ utilisée pour récupérer common data pour le twig commun __""
    ): Response {

                    // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      

        $donneesCommunes = $this->getDonneesCommunes($doctrine);

        // Create the form for updating roles and email
        $form = $this->createForm(UtilisateurRoleType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Les rôles et le courriel de l\'utilisateur ont été mis à jour.');

            return $this->redirectToRoute('institution_index_ajouter');
        }

        return $this->render('Pages_modifications/modifier_roles.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
            ...$donneesCommunes,
        ]);
    }




    #[Route("/utilisateur/{id}/modifiercommentaire", name: 'utilisateur_modifier')]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function modifier(
        int $id,
        UtilisateurRepository $utilisateurRepository,
        Request $request,
        EntityManagerInterface $em,
        ManagerRegistry $doctrine // _ utilisée pour récupérer common data pour le twig commun __""

    ): Response {

                    // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      

        $donneesCommunes = $this->getDonneesCommunes($doctrine);

        // Récupérer l'utilisateur en fonction de l'ID
        $utilisateur = $utilisateurRepository->find($id);
        // Vérifier si l'utilisateur existe
        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Créer un formulaire pour modifier uniquement le champ commentaire 
        $form = $this->createForm(UtilisateurCommentaireType::class, $utilisateur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Le commentaire de l\'utilisateur a été modifié avec succès!');
            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }

        return $this->render('Pages_modifications/modifier_commentaire_utilisateur.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
            'id' => $id,
            ...$donneesCommunes,
        ]);
    }

    #[Route("/utilisateur/{id}/modulecommentaire", name: 'module_commentaire')]
    #[IsGranted('ABONNEMENT_ACTIF')]
    public function modifiermodulecommentaire(
        int $id,
        UtilisateurInstitutionSessionModuleRepository $utilisateurInstitutionSessionModulesRepository,
        UtilisateurRepository $utilisateurRepository,
        Request $request,
        EntityManagerInterface $em,
        ManagerRegistry $doctrine // _ utilisée pour récupérer common data pour le twig commun __""
    ): Response {

    // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      


        $donneesCommunes = $this->getDonneesCommunes($doctrine);

        $utilisateurInstitutionSessionModule = $utilisateurInstitutionSessionModulesRepository->find($id);

        if (!$utilisateurInstitutionSessionModule) {
            throw $this->createNotFoundException('Module non trouvé');
        }
        $utilisateur = $utilisateurInstitutionSessionModule->getUtilisateur();
        $formcommentairemodule = $this->createForm(CommentaireModuleType::class, $utilisateurInstitutionSessionModule);
        $formcommentairemodule->handleRequest($request);

        if ($formcommentairemodule->isSubmitted() && $formcommentairemodule->isValid()) {
            $em->persist($utilisateurInstitutionSessionModule); 
            $em->flush();

            return $this->redirectToRoute('utilisateur_liste', ['id' => $utilisateur->getId()]);
        }

        return $this->render('Pages_modifications/modifier_module_commentairenote.html.twig', [
            'id' => $id, 
            'formcommentairemodule' => $formcommentairemodule->createView(),
            ...$donneesCommunes,
        ]);
    }
}
