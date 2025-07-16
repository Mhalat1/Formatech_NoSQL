<?php
namespace App\Tests;

use App\Tests\Utils\UtilisateurDeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Institution;


class FormulaireInvitation extends WebTestCase
{
    use UtilisateurDeTestTrait;

    public function testFormulaireInvitation(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();

        // Création de l'utilisateur
        $utilisateurtest = $this->CreeEtLogerUtilisateur($client);
        
        // Création de l'institution
        $institution = new Institution();
        $institution->setNom('Test Institution');
        $institution->setAdresse('Adresse de test');
        $institution->setTelephone('0663254145');
        $institution->setCourriel('institution@test.fr');
        
        $institution->addUtilisateur($utilisateurtest);
        
        $em->persist($institution);
        $em->flush();

        // Vérification que l'institution est bien en base
        $institution = $em->getRepository(Institution::class);
        $InstitutionEnBDD = $institution->findOneBy(['nom' => 'Test Institution']);
        $this->assertNotNull($InstitutionEnBDD, "L'institution n'a pas été sauvegardée en base");



        // PARTIE REMPLISSAGE FORMULAIRE INVITATION //

        $crawler = $client->request('GET', 'admin/invite');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Envoyer l\'invitation')->form();

        // ajoute email
        $form['invitation[email]'] = 'test'.uniqid().'@example.com';
        // ajoute institution
        $crawler->filter('select[name="invitation[institution]"] option[value!=""]')->first(); // Prend la première option disponible
        // ajoute date expiration
        $form['invitation[expireLe]'] = (new \DateTime('+7 days'))->format('Y-m-d H:i:s');
        
        $client->submit($form);
        $this->assertResponseRedirects();
    }
}