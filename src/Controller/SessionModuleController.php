<?php

namespace App\Controller;

use App\Entity\SessionModule;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurInstitutionSessionModule;
use App\Entity\Institution;
use App\Entity\Session;
use App\Entity\Module;
use App\Entity\JourHoraire;

use App\Form\UtilisateurInstitutionSessionModuleType;
use App\Form\SessionModuleType;
use App\Form\InstitutionCreationType;
use App\Form\SessionCreationType;
use App\Form\ModuleCreationType;
use App\Form\JourHoraireType;

use App\Repository\SessionModuleRepository;
use App\Repository\UtilisateurInstitutionSessionModuleRepository;  
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ModuleRepository;  
use App\Repository\SessionRepository;  
use App\Repository\InstitutionRepository;  
use App\Repository\UtilisateurRepository;  

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Form\InstitutionType;
use App\Form\ModuleType;
use App\Form\SessionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use App\PartieReutilisable\DonneesCommunes;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SessionModuleController extends AbstractController
{
    use DonneesCommunes;

    #[Route('/infosessionmodule', name: 'app_Session_Module')]
        #[IsGranted('ABONNEMENT_ACTIF')]
        // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides

    public function index(
        ModuleRepository $ModuleRepository,
        SessionRepository $SessionRepository,
        InstitutionRepository $InstitutionRepository,
        SessionModuleRepository $sessionModuleRepository,
        UtilisateurInstitutionSessionModuleRepository $utilisateurInstitutionSessionModuleRepository,
        UtilisateurRepository $UtilisateurRepository,
        Request $request,
        EntityManagerInterface $em,
        ManagerRegistry $doctrine 
    ): Response {

      



        $donneesCommunes = $this->getDonneesCommunes($doctrine);

        $utilisateurInstitutionSessionModules = $utilisateurInstitutionSessionModuleRepository->findAll();

        $modulesetsessions = $sessionModuleRepository->findAll();

        // PARTIE CREATION //

        // Créer un nouvel objet SessionModule pour l'ajout 
        $sessionModule = new SessionModule();
        $formism = $this->createForm(SessionModuleType::class, $sessionModule);

        $formism->handleRequest($request);

        if ($formism->isSubmitted() && $formism->isValid()) {
            $em->persist($sessionModule);
            $em->flush();
            $this->addFlash('success', 'Votre combinaisson Institution Session Module a bien été crée !');
            return $this->redirectToRoute('app_Session_Module');
        }


        $nombreutilisateurs = $utilisateurInstitutionSessionModuleRepository->comptageUtilisateurParSessionModule();


        $utilisateurInstitutionSessionModule = new UtilisateurInstitutionSessionModule();
        $formUtilisateurInstitutionSessionModule = $this->createForm(UtilisateurInstitutionSessionModuleType::class, $utilisateurInstitutionSessionModule);
        $formUtilisateurInstitutionSessionModule->handleRequest($request);

        // Vérifier si le formulaire UtilisateurInstitutionSessionModule est soumis et valide
        if ($formUtilisateurInstitutionSessionModule->isSubmitted() && $formUtilisateurInstitutionSessionModule->isValid()) {
            $em->persist($utilisateurInstitutionSessionModule);
            $em->flush();


            $this->addFlash('success', 'Votre combinaisson Utilisateur Module a bien été crée !');
            return $this->redirectToRoute('app_Session_Module');
        }


        // création d'une nouvelle institution
        $formInstitutionEntité = new Institution();
        $formInstitution = $this->createForm(InstitutionCreationType::class, $formInstitutionEntité);

        $formInstitution->handleRequest($request);
        if ($formInstitution->isSubmitted() && $formInstitution->isValid()) {

            $utilisateur_session = $this->getUser();
            $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateur_session);

            if (!$utilisateur) {
                $this->addFlash('error', 'Aucun utilisateur connecté !');
                return $this->redirectToRoute('app_Session_Module');
            }

            $formInstitutionEntité->setCreateurId($utilisateur->getId());

            // 2. Incrémenter le compteur de l'utilisateur
            $utilisateur->incrementCompteur();

            // Enregistrement en base de données
            $em->persist($formInstitutionEntité);
            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash('success', 'Votre Institution a bien été créée !');
            return $this->redirectToRoute('app_Session_Module');
        }



        // création d'une nouvelle SESSION
        $formSessionEntité = new Session();
        $formSession = $this->createForm(SessionCreationType::class, $formSessionEntité);

        $formSession->handleRequest($request);
        if ($formSession->isSubmitted() && $formSession->isValid()) {
            // Enregistrement en base de données
            $em->persist($formSessionEntité);
            $em->flush();

            // Ajout d'un message flash
            $this->addFlash('success', 'Votre Session a bien été crée !');
            return $this->redirectToRoute('app_Session_Module');
        };



        // création d'une nouvelle MODULE
        $formModuleEntité = new Module();
        $formModule = $this->createForm(ModuleCreationType::class, $formModuleEntité);

        $formModule->handleRequest($request);
        if ($formModule->isSubmitted() && $formModule->isValid()) {
            // Enregistrement en base de données
            $em->persist($formModuleEntité);
            $em->flush();

            // Ajout d'un message flash
            $this->addFlash('success', 'Votre Module a bien été crée !');
            return $this->redirectToRoute('app_Session_Module');
        };






        $jourHoraire = new JourHoraire();
        $formHoraire = $this->createForm(JourHoraireType::class, $jourHoraire);

        $formHoraire->handleRequest($request);

        if ($formHoraire->isSubmitted() && $formHoraire->isValid()) {
            // Récupérer le champ UtilisateurInstitutionSessionModule depuis le formulaire
            $uisModule = $formHoraire->get('UtilisateurInstitutionSessionModule')->getData();

            if ($uisModule) {
                $jourHoraire->setUtilisateurInstitutionSessionModule($uisModule);
            } else {
                $this->addFlash('error', 'Impossible de récupérer le UtilisateurInstitutionSessionModule associé.');
                return $this->redirectToRoute('app_Session_Module');
            }

            $em->persist($jourHoraire);
            $em->flush();

            $this->addFlash('success', 'Votre association horaires au module a bien été créée !');
            return $this->redirectToRoute('app_Session_Module');
        }



        // PARTIE SUPPRESION //

        // SUPPRIMER UN MODULE //
        $supprimerModule = $request->get('supprimer_Module');
        if ($supprimerModule) {
            $ModuleToSupprimer = $ModuleRepository->find($supprimerModule);
            if ($ModuleToSupprimer) {
                $em->remove($ModuleToSupprimer);
                $em->flush();
            }
            $this->addFlash('success', 'Module Supprimé !');
            return $this->redirectToRoute('app_Session_Module');
        }


        // SUPPRIMER UN SESSION //

        $supprimerSession = $request->get('supprimer_Session');
        if ($supprimerSession) {
            $SessionToSupprimer = $SessionRepository->find($supprimerSession);
            if ($SessionToSupprimer) {
                $em->remove($SessionToSupprimer);
                $em->flush();
            }
            $this->addFlash('success', 'Session Supprimé !');
            return $this->redirectToRoute('app_Session_Module');
        }


        // SUPPRIMER UN INSTITUTION //

        $supprimerInstitution = $request->get('supprimer_Institution');
        if ($supprimerInstitution) {
            $InstitutionToSupprimer = $InstitutionRepository->find($supprimerInstitution);
            if ($InstitutionToSupprimer) {
                $em->remove($InstitutionToSupprimer);
                $em->flush();
            }
            $this->addFlash('success', 'Institution Supprimé !');
            return $this->redirectToRoute('app_Session_Module');
        }


        // SUPPRIMER LA COMBINAISON iNSTITUTION SESSION MODULE //

        $supprimerISM = $request->get('supprimer_ISM');
        if ($supprimerISM) {
            $sessionModuleASupprimer = $sessionModuleRepository->find($supprimerISM);
            if ($sessionModuleASupprimer) {
                $em->remove($sessionModuleASupprimer);
                $em->flush();
            }
            $this->addFlash('success', 'SessionModule Supprimé !');
            return $this->redirectToRoute('app_Session_Module');
        }

        // SUPPRIMER LA COMBINAISON UTILISATEUR INSTITUTION SESSION MODULE // 

        $supprimerUtilisateurUISM = $request->get('deleteUtilisateurId');
        if ($supprimerUtilisateurUISM) {
            $UtilisateurASupprimer = $utilisateurInstitutionSessionModuleRepository->find($supprimerUtilisateurUISM);
            if ($UtilisateurASupprimer) {
                $em->remove($UtilisateurASupprimer);
                $em->flush();
            }
            return $this->redirectToRoute('app_Session_Module');
        }

        // Préparer les résultats pour la vue
        $resultats = [];
        foreach ($modulesetsessions as $module_et_session) {
            $resultats[] = [
                'moduleId' => $module_et_session->getId(),
                'moduleNom' => $module_et_session->getModule()->getNom(),
                'moduleDescription' => $module_et_session->getModule()->getDescription(),
                'moduleDateDebut' => $module_et_session->getModule()->getDateDebut(),
                'moduleDateFin' => $module_et_session->getModule()->getDateFin(),
                'sessionId' => $module_et_session->getSession()->getId(),
                'sessionNom' => $module_et_session->getSession()->getNom(),
                'sessionType' => $module_et_session->getSession()->getType(),
                'sessionDateDebut' => $module_et_session->getSession()->getDateDebut(),
                'sessionDateFin' => $module_et_session->getSession()->getDateFin(),
                'sessionDescription' => $module_et_session->getSession()->getDescription(),
                'institutionId' => $module_et_session->getInstitution()->getId(),
                'institutionNom' => $module_et_session->getInstitution()->getNom(),
                'institutionAdresse' => $module_et_session->getInstitution()->getAdresse(),
                'institutionTelephone' => $module_et_session->getInstitution()->getTelephone(),
                'institutionCourriel' => $module_et_session->getInstitution()->getCourriel(),


            ];
        }
        $modules = $ModuleRepository->findAll();
        $sessions = $SessionRepository->findAll();
        $institutions = $InstitutionRepository->findAll();
        $utilisateurs = $UtilisateurRepository->findAll();

        return $this->render('Pages_principaux/page_sessionmodule.html.twig', [
            'resultats' => $resultats,
            'formism' => $formism->createView(),
            'formUtilisateurInstitutionSessionModule' => $formUtilisateurInstitutionSessionModule->createView(),
            'utilisateurInstitutionSessionModules' => $utilisateurInstitutionSessionModules, // Passer les données récupérées
            'formInstitution' => $formInstitution->createView(),
            'formSession' => $formSession->createView(),
            'formModule' => $formModule->createView(),
            'formHoraire'   => $formHoraire->createView(),
            'modules' => $modules,
            'sessions' => $sessions,
            'institutions' => $institutions,
            'utilisateurs' => $utilisateurs,
            ...$donneesCommunes,
            'nombreutilisateurs' => $nombreutilisateurs,
        ]);
    }


    #[Route('/sessionmodule/modifier/{id}', name: 'modifier_session_module')]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function modifierSessionModule(
        int $id,
        SessionModuleRepository $sessionModuleRepository,
        Request $request,
        EntityManagerInterface $em,

        ManagerRegistry $doctrine // _ utilisée pour récupérer common data pour le twig commun __""

    ): Response {



        $donneesCommunes = $this->getDonneesCommunes($doctrine);
        $sessionModule = $sessionModuleRepository->find($id);
        if (!$sessionModule) {
            throw $this->createNotFoundException('SessionModule non trouvé');
        }

        // Créer et gérer le formulaire de modification
        $formism = $this->createForm(SessionModuleType::class, $sessionModule);
        $formism->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($formism->isSubmitted() && $formism->isValid()) {
            $em->flush();  

            $this->addFlash('success', 'SessionModule modifié avec succès!');
            return $this->redirectToRoute('app_Session_Module'); 
        }

        // Passer le formulaire au template Twig pour le rendu
        return $this->render('Pages_modifications/modifier_sessionmodule.html.twig', [
            'form' => $formism->createView(),
            'sessionModule' => $sessionModule,
            ...$donneesCommunes,
        ]);
    }



    // Nouvelle route pour modifier un UtilisateurInstitutionSessionModule
    #[Route('/utilisateursessionmodule/modifier/{id}', name: 'modifier_utilisateur_institution_session_module')]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function modifierUtilisateurInstitutionSessionModule(
        int $id,
        UtilisateurInstitutionSessionModuleRepository $utilisateurInstitutionSessionModuleRepository,
        Request $request,
        EntityManagerInterface $em,
        ManagerRegistry $doctrine // _ utilisée pour récupérer common data pour le twig commun __""

    ): Response {



        $donneesCommunes = $this->getDonneesCommunes($doctrine);
        // Récupérer l'instance du module utilisateur en fonction de l'ID
        $utilisateurInstitutionSessionModule = $utilisateurInstitutionSessionModuleRepository->find($id);

        if (!$utilisateurInstitutionSessionModule) {
            throw $this->createNotFoundException('UtilisateurInstitutionSessionModule non trouvé');
        }

        // Créer le formulaire pour la modification
        $form_modif = $this->createForm(UtilisateurInstitutionSessionModuleType::class, $utilisateurInstitutionSessionModule);
        $form_modif->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form_modif->isSubmitted() && $form_modif->isValid()) {
            // Sauvegarder les modifications
            $em->persist($utilisateurInstitutionSessionModule);
            $em->flush();

            // Ajouter un message de succès et rediriger vers la page de la session module
            $this->addFlash('success', 'UtilisateurInstitutionSessionModule modifié avec succès!');
            return $this->redirectToRoute('app_Session_Module');
        }

        // Rendre la vue avec le formulaire
        return $this->render('Pages_modifications/liaison_utilisateur_sessionmodule.html.twig', [
            'form_modif' => $form_modif->createView(),
            'utilisateurInstitutionSessionModule' => $utilisateurInstitutionSessionModule,
            ...$donneesCommunes,
        ]);
    }


    // _________Parites modifications Institutions Session Module___________  //

    #[Route('/session/{id}/modifier', name: 'session_modifier')]
    #[IsGranted('ABONNEMENT_ACTIF')]

    public function modifierSession(
        Request $request,
        Session $session,
        EntityManagerInterface $em,
        ManagerRegistry $doctrine, // _ utilisée pour récupérer common data pour le twig commun __""
    ): Response {

   
        $donneesCommunes = $this->getDonneesCommunes($doctrine);

        $form_session_modifier = $this->createForm(SessionType::class, $session, [
            'data_class' => Session::class,
        ]);
        $form_session_modifier->handleRequest($request);

        if ($form_session_modifier->isSubmitted() && $form_session_modifier->isValid()) {
            $em->flush();  

            $this->addFlash('success', 'Session modifiée avec succès!');  
            return $this->redirectToRoute('institution_index_ajouter');  
        }

        return $this->render('Pages_modifications/modifier_session.html.twig', [
            'form_session_modifier' => $form_session_modifier->createView(),
            'session' => $session,
            ...$donneesCommunes,
        ]);
    }


    #[Route('/institution/{id}/modifier', name: 'institution_modifier')]
        #[IsGranted('ABONNEMENT_ACTIF')]
    public function modifierInstitution(
        Request $request,
        Institution $institution,
        // equivalent à $institution = $em->getRepository(Institution::class)->find($id);
        EntityManagerInterface $em,
        ManagerRegistry $doctrine // _ utilisée pour récupérer common data pour le twig commun __""
    ): Response {

 

        $donneesCommunes = $this->getDonneesCommunes($doctrine);
        $form_institution = $this->createForm(InstitutionType::class, $institution);
        $form_institution->handleRequest($request);

        
        if ($form_institution->isSubmitted() && $form_institution->isValid()) {
            $em->flush(); 

            $this->addFlash('success', 'Institution modifiée avec succès!');  
            return $this->redirectToRoute('institution_index_ajouter');  
        }

        return $this->render('Pages_modifications/modifier_institution.html.twig', [
            'form_institution' => $form_institution->createView(),
            'institution' => $institution,
            ...$donneesCommunes,
        ]);
    }

    #[Route('/module/{id}/modifier', name: 'module_modifier')]
    #[IsGranted('ABONNEMENT_ACTIF')]
    public function modifierModule(
        Request $request,
        EntityManagerInterface $em,
        ManagerRegistry $doctrine // _ utilisée pour récupérer common data pour le twig commun __""
    ): Response {




        $donneesCommunes = $this->getDonneesCommunes($doctrine);

        $module = new Module();
        $form_module = $this->createForm(ModuleType::class, $module, [
            'data_class' => Module::class, 
        ]);
        $form_module->handleRequest($request);

        if ($form_module->isSubmitted() && $form_module->isValid()) {
            $em->persist($module);
            $em->flush();

            $this->addFlash('success', 'Module modifié avec succès!');
            return $this->redirectToRoute('app_Session_Module');
        }

        return $this->render('Pages_modifications/modifier_module.html.twig', [
            'form_module' => $form_module->createView(),
            'module' => $module,
            ...$donneesCommunes,
        ]);
    }
}
