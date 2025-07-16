<?php

namespace App\Tests;

use App\Tests\Utils\UtilisateurDeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Institution;
use App\Entity\Session;

class FormulaireSession extends WebTestCase
{
    use UtilisateurDeTestTrait;


    public function testCreationSuppressionSession(): void
    {

        //doc symfony utilise le terme client le navigateur virtuel je le garde ainsi 
        $client = static::createClient();

        $this->CreeEtLogerUtilisateur($client);

        $crawler = $client->request('GET', '/infosessionmodule');
        $this->assertResponseIsSuccessful();


        $sessionData = [
            'nom' => 'Session Test',
            'type' => 'Formation',
            'date_debut' => '2023-01-01',
            'date_fin' => '2023-12-31',
            'description' => 'Description de test'
        ];

        $form = $crawler->selectButton('Créer Session')->form();
        $client->submit($form, [
            'session_creation[nom]' => $sessionData['nom'],
            'session_creation[type]' => $sessionData['type'],
            'session_creation[date_debut]' => $sessionData['date_debut'],
            'session_creation[date_fin]' => $sessionData['date_fin'],
            'session_creation[description]' => $sessionData['description']
        ]);
        $this->assertResponseRedirects('/infosessionmodule');

        // redirection pour verifier la creation
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        // verifier creation depuis bdd
        $em = $client->getContainer()->get('doctrine')->getManager();
        $session = $em->getRepository(Session::class)
            ->findOneBy(['nom' => 'Session Test']);
        $this->assertNotNull($session, 'Session non cree');
        $id = $session->getId();



        // TEST DE SUPPRESION // 


        // 4. Tester la suppression requête HTTP POST pour suppresion
        //champ <input type="hidden" name="supprimer_Institution"> dans formulaire HTML, $id de l'element à supprimer 
        $client->request('POST', '/infosessionmodule', [
            'supprimer_Institution' => $id
        ]);

        //Vérifier la redirection
        $this->assertResponseRedirects();
        $client->followRedirect();

        //Vérifier la suppression en base
        $em->clear(); //Important pour éviter le cache
        $institutionSupprime = $em->getRepository(Institution::class)->find($id);
        $this->assertNull($institutionSupprime, "L'institution avec l'ID $id devrait être supprimée");
    }
}
