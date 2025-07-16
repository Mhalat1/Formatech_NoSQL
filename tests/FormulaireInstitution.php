<?php

namespace App\Tests;

use App\Tests\Utils\UtilisateurDeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Institution;

class FormulaireInstitution extends WebTestCase
{
    use UtilisateurDeTestTrait;


    public function testCreationSuppresionInstitution(): void
    {

        $client = static::createClient();

        $user = $this->CreeEtLogerUtilisateur($client);

        $crawler = $client->request('GET', '/infosessionmodule');
        $this->assertResponseIsSuccessful();


        $institutionData = [
            'nom' => 'Institution Test',
            'adresse' => 'Formation',
            'telephone' => '0663254112',
            'courriel' => 'test@est.test'
        ];

        $form = $crawler->selectButton('Créer Institution')->form();
        $client->submit($form, [
            'institution_creation[nom]' => $institutionData['nom'],
            'institution_creation[adresse]' => $institutionData['adresse'],
            'institution_creation[telephone]' => $institutionData['telephone'],
            'institution_creation[courriel]' => $institutionData['courriel']
        ]);
        $this->assertResponseRedirects('/infosessionmodule');

        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $em = $client->getContainer()->get('doctrine')->getManager();
        $institution = $em->getRepository(Institution::class)
            ->findOneBy(['nom' => 'Institution Test']);
        $this->assertNotNull($institution, 'Institution non cree');
        $id = $institution->getId();



        // TEST DE SUPPRESION // 


        // 4. Tester la suppression
        $client->request('POST', '/infosessionmodule', [
            'supprimer_Institution' => $id
        ]);

        // 5. Vérifier la redirection
        $this->assertResponseRedirects();
        $client->followRedirect();

        // 6. Vérifier la suppression en base
        $em->clear(); // Important pour éviter le cache
        $institutionSupprime = $em->getRepository(Institution::class)->find($id);
        $this->assertNull($institutionSupprime, "L'institution avec l'ID $id devrait être supprimée");
    }
}