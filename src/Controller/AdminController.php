<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServicesType;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function administration(): Response
    {

        return $this->render('admin/admin.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/prestations/edit/{id?}', name: 'services_edit', methods: ['GET', 'POST'])]
    public function editService(Request $request, ServiceRepository $serviceRepository, EntityManagerInterface $entityManagerInterface, ?int $id = null): Response
    {
        $service = $id ? $serviceRepository->find($id) : new Service(); // Si $id est null, je crée une nouvelle instance de Service

        if ($request->isMethod('POST')) {
            // Requête AJAX
            $data = json_decode($request->getContent()); // Je récupère les données envoyées en JSON
            //print_r($data);
            $service = $serviceRepository->find($id); // Je récupère l'entité correspondant à l'id
            $service->setNom($data->nom);
            $service->setPrix($data->prix);
            $service->setDuree($data->duree);
            $entityManagerInterface->persist($service);
            $entityManagerInterface->flush();
            return new JsonResponse(['success' => true]); // Je retourne une réponse en JSON
        } else {
            // Procédure normale d'AJAX
            $form = $this->createForm(ServicesType::class, $service); // Je crée le formulaire
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) { // Si le formulaire est soumis et valide
                $entityManagerInterface->persist($service);
                $entityManagerInterface->flush();
                $this->addFlash('success', 'La prestation a bien été ajoutée');
            }

            return $this->render('admin/services_edit.html.twig', [
                'serviceForm' => $form->createView(), // Je passe la vue du formulaire à Twig
            ]);
        }
    }

    #[Route('/prestations', name: 'prestations', methods: ['GET', 'POST'])]
    public function prestations(ServiceRepository $serviceRepository): Response
    {
        $services = $serviceRepository->findAll(); // Je récupère toutes les prestations
        $form = []; // Je crée un tableau vide

        foreach ($services as $service) { // Pour chaque prestation
            $form[$service->getId()] = $this->createForm(ServicesType::class, $service)->createView(); // Je crée le formulaire et je passe la vue à Twig
        }

        return $this->render('admin/prestations.html.twig', [ // Je passe les données à Twig
            'services' => $services, // Je passe les prestations
            'serviceForms' => $form, // Je passe les vues des formulaires
        ]);
    }
}
