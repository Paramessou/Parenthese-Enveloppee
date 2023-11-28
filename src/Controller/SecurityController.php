<?php

namespace App\Controller;

use App\Service\RateLimiterService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class SecurityController extends AbstractController
{
    private $rateLimiter; // Injecte le service RateLimiterService


    public function __construct(RateLimiterService $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $lastUsername = $authenticationUtils->getLastUsername();
        $cacheKey = md5($lastUsername);
        $now = new \DateTime();

        if ($cacheKey && $this->rateLimiter->hasExceededMaxAttempts($cacheKey)) {
            $lastAttemptTime = $this->rateLimiter->getLastAttemptTime($cacheKey);
            if ($lastAttemptTime !== null && $now->getTimestamp() - $lastAttemptTime->getTimestamp() >= RateLimiterService::BLOCK_TIME) {
                $errorMessage = 'Vous avez dépassé le nombre maximum de tentatives de connexion. Veuillez réessayer dans ' . RateLimiterService::BLOCK_TIME . ' secondes.';
                $error = new CustomUserMessageAuthenticationException($errorMessage);
                return $this->render('security/login.html.twig', [
                    'last_username' => $lastUsername,
                    'error' => $error,
                    'block_time' => RateLimiterService::BLOCK_TIME
                ]);
            }
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        if ($cacheKey && $error !== null) {
            $this->rateLimiter->incrementAttempts($cacheKey);
        }

        if ($cacheKey && $this->rateLimiter->hasExceededMaxAttempts($cacheKey)) {
            $errorMessage = 'Vous avez dépassé le nombre maximum de tentatives de connexion.';
            $error = new CustomUserMessageAuthenticationException($errorMessage);
            throw $error;
        }

        if ($cacheKey && $error === null) {
            $this->rateLimiter->resetAttempts($cacheKey);
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'block_time' => RateLimiterService::BLOCK_TIME
        ]);
    }
    // #[Route(path: '/login', name: 'app_login')]
    // public function login(AuthenticationUtils $authenticationUtils): Response
    // {
    //     // if ($this->getUser()) {
    //     //     return $this->redirectToRoute('target_path');
    //     // }

    //     // get the login error if there is one
    //     $error = $authenticationUtils->getLastAuthenticationError();
    //     // last username entered by the user
    //     $lastUsername = $authenticationUtils->getLastUsername();

    //     return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    // }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion de votre pare-feu.');
    }
}
