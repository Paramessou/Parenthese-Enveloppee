<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

use App\Security\LoginFormAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticatorTest extends TestCase
{
    public function testAuthenticate()
    {
        // Simule une requête avec un email et un mot de passe
        $request = new Request([], [], [], [], [], [], '{"email":"user@example.com","password":"password123"}');

        // Simule les dépendances : UrlGeneratorInterface et SessionInterface
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->any())->method('start');
        $request->setSession($session);

        $authenticator = new LoginFormAuthenticator($urlGenerator);

        // Appelle la méthode authenticate et fournit les dépendances simulées
        $passport = $authenticator->authenticate($request);

        // Vérifie qu'un passport est retourné
        $this->assertInstanceOf(Passport::class, $passport);
    }
}
