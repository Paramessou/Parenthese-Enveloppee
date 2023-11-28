<?php

namespace App\Controller;

use App\Service\RateLimiterService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $rateLimiter;

    public function __construct(RateLimiterService $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère le dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();
        $cacheKey = md5($lastUsername);
        // print_r($cacheKey);

        // Récupère l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Si une erreur de connexion s'est produite, augmente le nombre de tentatives de connexion
        if ($cacheKey && $error !== null) {
            // print_r($cacheKey);
            $this->rateLimiter->incrementAttempts($cacheKey);
        }

        // Controle si l'utilisateur a dépassé le nombre maximum de tentatives de connexion
        if ($cacheKey && $this->rateLimiter->hasExceededMaxAttempts($cacheKey)) {
            // print_r($cacheKey);
            // var_dump('trop de tentatives de connexion');
            return $this->render('security/login.html.twig', [
                'last_username' => $lastUsername,
                'error' => 'Vous avez dépassé le nombre maximum de tentatives de connexion.',
            ]);
        }

        $this->rateLimiter->resetAttempts($cacheKey);

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
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
