<?php
namespace App\Tests\Utils;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait UtilisateurDeTestTrait
{

    //$client c'est une instance de KernelBrowser, le simulateur de navigateur de Symfony 
    public function CreationUtilisateurdeTest(KernelBrowser $client): Utilisateur
{

    // on récupere dans l'isntance kernelbrowser entitymanager
    $em = $client->getContainer()->get('doctrine')->getManager();
    
    $testEmail = $email ?? 'jean.dupont'.uniqid().'@test.com';
    
    $crawler = $client->request('GET', '/inscription');
    $this->assertResponseIsSuccessful();

    $form = $crawler->selectButton('inscription_form[inscrire]')->form();

    $client->submit($form, [
        'inscription_form[institutionNom]' => 'Test Institution',
        'inscription_form[institutionAdresse]' => '123 Test Street',
        'inscription_form[institutionTelephone]' => '0123456789',
        'inscription_form[institutionCourriel]' => 'institution@test.com',
        'inscription_form[prenom]' => 'Jean',
        'inscription_form[nom]' => 'Dupont',
        'inscription_form[courriel]' => $testEmail,
        'inscription_form[telephone]' => '0612345678',
        'inscription_form[dateNaissance]' => '1990-01-01',
        'inscription_form[motdepasse]' => 'Password123!',
        'inscription_form[agreeTerms]' => true,
    ]);

    $utilisateurTest = $em->getRepository(Utilisateur::class)
        ->findOneBy(['courriel' => $testEmail]);
    
    $this->assertNotNull($utilisateurTest, 'utilisateur non trouvé en BDD');
    
    $utilisateurTest->setDateFinAbonnement(new \DateTime('+1 month'));
    $utilisateurTest->setRoles(["ROLE_ADMIN"]);
    $em->flush();

    return $utilisateurTest;
}

public function CreeEtLogerUtilisateur($client): Utilisateur
{
    //Création de l'utilisateur avec les valeurs par défaut
    $utilisateurTest = $this->CreationUtilisateurdeTest($client);
    
    //Connexion de l'utilisateur
    $client->loginUser($utilisateurTest);
    
    //Retour de l'utilisateur connecté
    return $utilisateurTest;
}
}