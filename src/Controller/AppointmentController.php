<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/rendez-vous')]
class AppointmentController extends AbstractController
{
    #[Route('/', name: 'app_appointment_index', methods: ['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointmentRepository->findAll(),
        ]);
    }

    #[Route('/nouveauRdv/{start}', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, $start = null): Response
    {
        if (!$this->getUser() || !in_array('ROLE_USER', $this->getUser()->getRoles()) && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) { // Si l'utilisateur n'est pas connecté ou n'a pas le rôle ROLE_USER ou ROLE_ADMIN
            return $this->redirectToRoute('app_login'); // Redirige vers la page de connexion
        }

        $appointment = new Appointment(); // Création d'un nouveau rendez-vous
        $appointment->setDateCreationRdv(new \DateTime()); // Renseigne la date de création du rendez-vous
        $appointment->setStatus('default'); // Renseigne le statut du rendez-vous
        $appointment->setUserId($this->getUser()); // Renseigne l'utilisateur connecté

        if ($start) { // Si la date de début est renseignée
            $appointment->setDebut(new \DateTime($start)); // Renseigne la date de début du rendez-vous
        }

        $form = $this->createForm(AppointmentType::class, $appointment, [ // Création du formulaire de rendez-vous
            'user' => $this->getUser(), // Renseigne l'utilisateur connecté
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            return $this->redirectToRoute('appointment_index');
        }

        return $this->render('appointment/rdv.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment = null): Response
    {
        if (!$appointment) {
            throw $this->createNotFoundException('Aucun rendez-vous trouvé avec cet ID');
        }
        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $appointment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($appointment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_appointment_index', [], Response::HTTP_SEE_OTHER);
    }
}
