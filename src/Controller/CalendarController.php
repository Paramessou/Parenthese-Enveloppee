<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/calendrier/rendez-vous', name: 'app_calendar_appointment')]
    public function appointments(AppointmentRepository $appointmentRepository, EntityManagerInterface $em): JsonResponse
    {
        $appointments = $appointmentRepository->findAll(); // Récupère tous les rendez-vous
        $appointmentsArray = []; // Initialise un tableau vide
        $now = new \DateTime(); // Récupère la date du jour

        foreach ($appointments as $appointment) { // Pour chaque rendez-vous
            if ($appointment->getFin() < $now) { // Si la date de fin du rendez-vous est inférieure à la date du jour
                $appointment->setStatus('finish'); // Renseigne le statut du rendez-vous
                $em->persist($appointment); // Enregistre le rendez-vous
                $em->flush(); // Enregistre les données en base de données
            }
            $appointmentsArray[] = [ // Ajoute un tableau avec les données du rendez-vous
                'id' => $appointment->getId(), // Récupère l'id du rendez-vous
                'start' => $appointment->getDebut()->format('Y-m-d H:i:s'), // Récupère la date de début du rendez-vous
                'end' => $appointment->getFin()->format('Y-m-d H:i:s'), // Récupère la date de fin du rendez-vous
                'backgroundColor' => $appointment->getStatus() === 'default' ? '#58d58d' : '#af7372', // Récupère la couleur du rendez-vous
            ];
        }

        return new JsonResponse($appointmentsArray);
    }
}
