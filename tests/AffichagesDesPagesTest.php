<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AffichagesDesPagesTest extends WebTestCase
{
    public function testConnexionPageConnexion(): void
    {
        $client = static::createClient();
        $client->request('GET', '/connexion');
        
    }

    public function testDeconnexionPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/deconnexion');
    }

    public function testAccueilPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
    }

}