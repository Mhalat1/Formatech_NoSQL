<?php
namespace App\Tests;

use App\Tests\Utils\UtilisateurDeTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeconnexionTest extends WebTestCase
{
    use UtilisateurDeTestTrait;
    

    public function testDeconnexion(): void
    {
        $client = static::createClient();
        
        $user = $this->CreeEtLogerUtilisateur($client);
        
        $client->request('GET', '/deconnexion');

        //Vérifier la redirection vers la page de connexion
        $this->assertResponseRedirects(
            '/connexion'
        );

    }
}
