<?php
namespace App\tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Utilisateur;

class InscriptionTest extends WebTestCase
{


public function testSoumissionInscription()
{
    $client = static::createClient();
    $em = $client->getContainer()->get('doctrine')->getManager();
    
    $comptageInitiale = count($em->getRepository(Utilisateur::class)->findAll());

    $crawler = $client->request('GET', '/inscription');
    $this->assertResponseIsSuccessful();

    $form = $crawler->selectButton('inscription_form[inscrire]')->form();

    // Données de test
    $testEmail = 'jean.dupont'.uniqid().'@test.com'; // Email unique
    $formData = [
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
    ];

    // Soumettre le formulaire
    $client->submit($form, $formData);

    // Vérifier que l'utilisateur a été ajouté en BDD
    $uilisateur = $em->getRepository(Utilisateur::class);
    $nouvelUtilisateur = $uilisateur->findOneBy(['courriel' => $testEmail]);
    
    $this->assertNotNull($nouvelUtilisateur, 'L\'utilisateur n\'a pas été trouvé en base de données');
    $this->assertEquals('Jean', $nouvelUtilisateur->getPrenom());
    $this->assertEquals('Dupont', $nouvelUtilisateur->getNom());
    
    // Vérifier que le compte a augmenté de 1
    $this->assertCount(
        $comptageInitiale + 1, 
        $uilisateur->findAll(),
        'Le nombre d\'utilisateurs n\'a pas augmenté comme attendu'
    );

        // Définir la date de fin d'abonnement (1 mois plus tard)
        $dateFinAbonnement = new \DateTime('+1 month');
        $nouvelUtilisateur->setDateFinAbonnement($dateFinAbonnement);
        $em->flush();

        //Connexion avec le nouvel utilisateur
        $client->loginUser($nouvelUtilisateur);

        //Vérification de la connexion
        $client->request('GET', '/infosessionmodule'); 
        $this->assertResponseIsSuccessful();

    }
}
