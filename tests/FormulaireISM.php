<?php

namespace App\Tests;

use App\Tests\Utils\UtilisateurDeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Institution;
use App\Entity\Session;
use App\Entity\Module;
use App\Entity\SessionModule;

class FormulaireISM extends WebTestCase
{
    use UtilisateurDeTestTrait;


    //Si une méthode ne retourne rien, il faut explicitement le déclarer avec void
    public function testCreationSuppressionInstitutionSessionModule(): void
    {
        $client = static::createClient();

        $this->CreeEtLogerUtilisateur($client);

        $client->request('GET', '/infosessionmodule');
        $this->assertResponseIsSuccessful();

        $em = $client->getContainer()->get('doctrine')->getManager();

        $module = new Module();
        $module->setNom('Module Test');
        $module->setCommentaire('Requette get test sessionModule');
        $module->setDescription('DescriptRequette get test sessionModuleiontest');
        $module->setDateDebut(new \DateTime('2023-01-01'));
        $module->setDateFin(new \DateTime('2023-12-31'));
        $em->persist($module);

        $session = new Session();
        $session->setNom('Session Test');
        $session->setType('Requette get test sessionModule');
        $session->setDescription('Requette get test sessionModule');
        $session->setDateDebut(new \DateTime('2023-01-01'));
        $session->setDateFin(new \DateTime('2023-12-31'));
        $em->persist($session);

        $institution = new Institution();
        $institution->setNom('Test Institution');
        $institution->setAdresse('Requette get test sessionModule');
        $institution->setTelephone('0663254145');
        $institution->setCourriel('Institution@Test.fr');
        $em->persist($institution);

        $em->flush();


        $crawler = $client->request('GET', '/infosessionmodule');

        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Créez votre association institution session module')->form();

        $formData = [
            'session_module[module]' => $module->getId(),
            'session_module[session]' => $session->getId(),
            'session_module[institution]' => $institution->getId()
        ];

        $client->submit($form, $formData);
        $this->assertResponseRedirects('/infosessionmodule');


        //Vérifie la création en base
        $sessionModule = $em->getRepository(SessionModule::class)->findOneBy([
            'module' => $module,
            'session' => $session,
            'institution' => $institution
        ]);
        $this->assertNotNull($sessionModule);



        // TEST DE SUPPRESION // 

        $id = $sessionModule->getId();
        $client->request('POST', '/infosessionmodule', [
            'supprimer_ISM' => $id
        ]);


        $this->assertResponseRedirects(); // Vérifie une redirection après suppression
        $client->followRedirect();


        //Vérifie que l'entité a bien été supprimée de la base
        $em = $client->getContainer()->get('doctrine')->getManager();
        $em->clear();
        $sessionModuleSupprime = $em->getRepository(SessionModule::class)->find($id);
        $this->assertNull($sessionModuleSupprime, "L'entité SessionModule avec l'ID $id devrait être supprimée");
    }
}