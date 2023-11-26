<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Service;
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
    #[Route('/show', name: 'app_appointment_index', methods: ['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        if ($this->getUser() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())) { // Si l'utilisateur n'est pas connecté ou n'a pas le rôle ROLE_USER ou ROLE_ADMIN
            $user = $this->getUser(); // Récupère l'utilisateur actuellement connecté

            // Récupère uniquement les rendez-vous de l'utilisateur connecté
            $appointments = $appointmentRepository->findBy(['userId' => $user]);

            return $this->render('appointment/mes_rdvs.html.twig', [
                'appointments' => $appointments,
            ]); // Redirige vers la page de connexion
        } else if ($this->getUser() && in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {

            return $this->render('appointment/index.html.twig', [
                'appointments' => $appointmentRepository->findAll(),
            ]);
        } else {
            return $this->redirectToRoute('app_login');
        }
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


        /*  $appointmentDate = $request->request->get('appointmentDate'); // Récupère la date du rendez-vous de la requête POST
            if ($appointmentDate) { // Si la date de début est renseignée
                $appointment->setDebut(new \DateTime($appointmentDate)); // Renseigne la date de début du rendez-vous
            }
        PENSER A SUPPRIMER /{start} et $start = null DE LA ROUTE ET if($start) ligne 58-60 pour la méthode POST */

        $fin = $request->request->get('appointment_fin'); // Récupère la date de fin du rendez-vous
        //print_r($fin);
        if ($fin !== null) { // Si la date de fin est renseignée
            $appointment->setFin(new \DateTime($fin)); // Renseigne la date de fin du rendez-vous
        }

        if ($start) { // Si la date de début est renseignée
            $appointment->setDebut(new \DateTime($start)); // Renseigne la date de début du rendez-vous
        }

        $form = $this->createForm(AppointmentType::class, $appointment, [ // Création du formulaire de rendez-vous
            'user' => $this->getUser(), // Renseigne l'utilisateur connecté
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //print_r('Le formulaire est soumis et valide');
            // print_r('Le formulaire est valide');
            if ($appointment->chevaucheHeure($entityManager)) {
                // print_r('Le formulaire est valide mais chevauche une heure');
                $this->addFlash('error', 'Un rendez-vous existe déjà sur cette plage horaire. Sélectionnez une autre date ou heure.');
            } else {
                // print_r('Le formulaire est valide et ne chevauche pas d\'heure');
                $entityManager->persist($appointment);
                $entityManager->flush();

                return $this->redirectToRoute('app_appointment_index');
            }
        }

        return $this->render('appointment/rdv.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment = null): Response
    {

        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur actuellement connecté
        $form = $this->createForm(AppointmentType::class, $appointment, [
            'user' => $appointment->getUserId(), // Renseigne l'utilisateur connecté
        ]);
        $form->handleRequest($request);

        if ($appointment->getUserId() !== $user && !in_array('ROLE_ADMIN', $user->getRoles())) { // Si l'utilisateur connecté n'est pas le propriétaire du rendez-vous
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette page'); // Lève une exception
        }

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
