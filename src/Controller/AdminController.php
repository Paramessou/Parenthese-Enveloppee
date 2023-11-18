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
        $service = $id ? $serviceRepository->find($id) : new Service();

        if ($request->isMethod('POST')) {
            // Requête AJAX
            $service = $serviceRepository->find($id);
            $service->setNom($request->request->get('nom'));
            $service->setPrix($request->request->get('prix'));
            $service->setDuree($request->request->get('duree'));
            $entityManagerInterface->persist($service);
            $entityManagerInterface->flush();
            return new JsonResponse(['success' => true]);
        } else {
            // Procédure normale d'AJAX
            $form = $this->createForm(ServicesType::class, $service);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManagerInterface->persist($service);
                $entityManagerInterface->flush();
                $this->addFlash('success', 'La prestation a bien été ajoutée');
            }

            return $this->render('admin/services_edit.html.twig', [
                'serviceForm' => $form->createView(),
            ]);
        }
    }

    #[Route('/prestations', name: 'prestations', methods: ['GET', 'POST'])]
    public function prestations(ServiceRepository $serviceRepository): Response
    {
        $services = $serviceRepository->findAll();
        $form = [];

        foreach ($services as $service) {
            $form[$service->getId()] = $this->createForm(ServicesType::class, $service)->createView();
        }

        return $this->render('admin/prestations.html.twig', [
            'services' => $services,
            'serviceForms' => $form,
        ]);
    }
}
