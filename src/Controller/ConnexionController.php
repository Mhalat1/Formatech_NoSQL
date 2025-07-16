<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ConnexionController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_connexion')]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {

        $erreure = $authenticationUtils->getLastAuthenticationError();
        $identifiant = $authenticationUtils->getLastUsername();

        return $this->render('connexion/connexion.html.twig', ['identifiant' => $identifiant, 'erreure' => $erreure]);
    }

    #[Route(path: '/deconnexion', name: 'app_deconnexion')]
    public function deconnexion(): void
    {
        // Cette méthode ne sera jamais exécutée
        // La deconnexion est gérée automatiquement par le firewall Symfony
        //config\packages\security.yaml
        throw new \LogicException('Cette méthode ne doit pas être appelée directement.');

    }
}