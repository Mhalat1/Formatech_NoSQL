<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\StripeClient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class StripeController extends AbstractController
{
#[Route('/abonnements', name: 'liste_abonnements')]
public function listeAbonnements(): Response
{    
    $abonnements = [
        'basique' => [
            'prix_id' => 'price_1RXTkLPG69PDCeC6IvbQTR31',
            'nom' => 'basique',
            'prix' => '9.99€/mois'
        ],
        'premium' => [
            'prix_id' => 'price_1RXUZEPG69PDCeC6CD5zNaBW',
            'nom' => 'premium', 
            'prix' => '19.99€/mois'
        ]
    ];
    
    return $this->render('paiement\choix_abonnement.html.twig', [
        'abonnements' => $abonnements,
        'stripe_public_key' => $_ENV['STRIPE_CLE_PUBLIC'] 
    ]);
}


    

    #[Route('/verfication/{prix_id}/{nom}', name: 'paiement_abonnement')]
    public function verfication(string $prix_id, string $nom): Response
    {
        $stripe = new StripeClient($_ENV['STRIPE_CLE_PRIVEE']);
        $typeAbonnement = $nom;

        //point d'entrée officiel de l'API Stripe pour créer des données 
        $creation_session = $stripe->checkout->sessions->create([
            'line_items' => [[
                'price' => $prix_id,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',

            'success_url' => $this->generateUrl('paiement_reussite'
            , [], UrlGeneratorInterface::ABSOLUTE_URL)
            . '?session_id={CHECKOUT_SESSION_ID}'
            . '&type_abonnement=' . urlencode(substr($typeAbonnement, 0, 20)), 

            'metadata' => [
                'type_abonnement' => $typeAbonnement
            ]
        ]);

        return $this->redirect($creation_session->url);
    }

    #[Route('/paiement/reussite', name: 'paiement_reussite')]
    public function reussite(Request $request, EntityManagerInterface $em,): Response
    {
        $sessionId = $request->query->get('session_id');
        $typeAbonnement = $request->query->get('type_abonnement');

        if (!$sessionId) {
            throw $this->createNotFoundException('Session ID manquant');
        }

        $stripe = new StripeClient($_ENV['STRIPE_CLE_PRIVEE']);

        //point d'entrée officiel de l'API Stripe pour récuperer des données 
        // valeurs officiel exemeple : chekout, sessions, amount_total etc 
        $session = $stripe->checkout->sessions->retrieve($sessionId);
        $montantTotal = $session->amount_total; 
        $montantEuros = $montantTotal / 100;

        //récup le bon utilisateur
        $utilisateur_session = $this->getUser();
        $utilisateur = $em->getRepository(Utilisateur::class)->find($utilisateur_session);

        // applique la focntion d'ajout +1 au compteur de sosucription 
        $utilisateur->incrementCompteurAbonnement();


        // augmente la durée de l'abonnement de 1mois par razpport à date d'aujourd'hui

        $utilisateur->setDateFinAbonnement(new \DateTime('+1 month')); 

        $em->flush();

        return $this->render('paiement/paiement_reussi.html.twig', [
            'session' => $session,
            'type_abonnement' => $typeAbonnement,
            'montantEuros' => $montantEuros
        ]);
    }
}

