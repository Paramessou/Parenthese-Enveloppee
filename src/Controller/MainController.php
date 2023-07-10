<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_home')]
    public function home()
    {
        echo "bienvenue sur la page d'accueil";
        die();
    }
    #[Route('/login', name: 'main_login')]
    public function login()
    {
        echo "login";
        die();
    }
}
