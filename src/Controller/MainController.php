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
}
