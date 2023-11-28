<?php

namespace App\Tests\Security;

use App\Service\RateLimiterService;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class RateLimiterServiceTest extends KernelTestCase
{
    private $client;
    private $rateLimiter;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->client = new HttpBrowser(HttpClient::create());
        $this->rateLimiter = static::getContainer()->get(RateLimiterService::class);
    }
    public function testRateLimiting(): void
    {
        var_dump($this->rateLimiter);
        $username = 'test@example.com';
        $cacheKey = md5($username);

        // Réinitialise les tentatives pour l'utilisateur
        $this->rateLimiter->resetAttempts($cacheKey);

        // Effectue 5 tentatives de connexion
        for ($i = 0; $i < 5; $i++) {
            $this->client->request('POST', '/login', [
                'username' => $username,
                'password' => 'wrong_password',
            ]);

            // Vérifie que l'utilisateur n'a pas encore dépassé le nombre maximum de tentatives
            $this->assertFalse($this->rateLimiter->hasExceededMaxAttempts($cacheKey));
        }

        // Effectue une 6ème tentative de connexion
        $this->client->request('POST', '/login', [
            'username' => $username,
            'password' => 'wrong_password',
        ]);

        // Vérifie que l'utilisateur a dépassé le nombre maximum de tentatives
        $this->assertTrue($this->rateLimiter->hasExceededMaxAttempts($cacheKey));
    }
}
