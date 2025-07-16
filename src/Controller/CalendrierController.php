<?php

namespace App\Controller;

use App\Repository\JourHoraireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CalendrierController extends AbstractController
{
    #[Route('/calendrier', name: 'calendrier')]
    #[IsGranted('ABONNEMENT_ACTIF')]

public function voirCalendrier(
    JourHoraireRepository $jourHoraireRepository,
    EntityManagerInterface $entityManager
): Response {

    // NOUVELLE VÉRIFICATION - Accès réservé aux abonnés valides
    // provient de Security/SubscriptionVoter.php
      

    

    // Récupérer tous les JourHoraire
    $jourHoraires = $jourHoraireRepository->findAll();

    // Pour chaque JourHoraire, on récupère ses UtilisateurInstitutionSessionModule associés
    $data = [];

    foreach ($jourHoraires as $jourHoraire) {
        $uism = $jourHoraire->getUtilisateurInstitutionSessionModule();

        if ($uism) {
            $sm = $uism->getSessionModule();
            $s = $sm->getSession();
            $i = $sm->getInstitution();
            $m = $sm->getModule();
            $u = $uism->getUtilisateur();

            // Rafraîchir pour éviter proxy
            $entityManager->refresh($uism);
            $entityManager->refresh($sm);
            $entityManager->refresh($s);
            $entityManager->refresh($i);
            $entityManager->refresh($m);
            $entityManager->refresh($u);
        }

        $data[] = [
            'jourHoraire' => $jourHoraire,
            'utilisateurInstitutionSessionModule' => $uism,
            'sessionModule' => $sm ?? null,
            'session' => $s ?? null,
            'institution' => $i ?? null,
            'module' => $m ?? null,
            'utilisateur' => $u ?? null,
        ];
    }

    return $this->render('Pages_principaux/page_emploidutemps.html.twig', [
        'data' => $data,
    ]);
}
}