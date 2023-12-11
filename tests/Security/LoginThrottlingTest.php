<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\BrowserKit\Cookie;

class LoginThrottlingTest extends WebTestCase
{
    public function testLoginThrottling(): void
    {
        $client = static::createClient();

        // Simule 6 tentatives de connexion avec un mauvais mot de passe
        for ($i = 0; $i < 6; $i++) {
            $client->request('GET', '/parenthese-enveloppee/public/login');
            echo $client->getResponse()->getContent();
            $form = $client->getCrawler()->filter('form')->form();

            $form['email'] = 'user@example.com';
            $form['password'] = 'wrong-password';
            $client->submit($form);
        }

        // Vérifie que l'utilisateur est bloqué après la 6ème tentative
        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $client->getResponse()->getStatusCode());
    }
}
