<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    #[Route('/', name: 'main_accueil')]
    public function accueil()
    {
        return $this->render('main/accueil.html.twig');
    }

    #[Route('/presentation', name: 'main_presentation')]
    public function presentation()
    {
        return $this->render('main/presentation.html.twig');
    }

    #[Route('/contact', name: 'main_contact')]
    public function contact()
    {
        return $this->render('main/contact.html.twig');
    }

    #[Route('/consentement-cookies', name: 'main_consent_cookies')]
    public function consentementCookies()
    {
        return $this->render('main/consentement_cookies.html.twig');
    }
}
