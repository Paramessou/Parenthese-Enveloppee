<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendar')]
    public function appointment(): Response
    {
        return $this->render('calendar/calendrier.html.twig', [
            'controller_name' => 'CalendarController',
        ]);
    }
}
