<?php

namespace App\Controller;

use App\Form\UserEditType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profil/edit', name: 'profile_edit')]
    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $managerRegistry): Response
    {
        $user = $this->getUser(); // récupère l'utilisateur connecté
        $form = $this->createForm(UserEditType::class, $user); // crée le formulaire avec l'utilisateur connecté

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // récupère les données du formulaire
            $data = $form->getData();

            // vérifie si l'utilisateur a fourni un nouveau mot de passe
            if ($data->getPlainPassword()) {
                // hash le nouveau mot de passe et le définit pour l'utilisateur
                $hashedPassword = $passwordHasher->hashPassword($user, $data->getPlainPassword());

                //définit le nouveau mot de passe pour l'utilisateur
                $user->setPassword($hashedPassword);
            }

            // sauvergarde l'utilisateur dans la BDD
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a bien été modifié');

            return $this->redirectToRoute('profile_show');
        }
        return $this->render('profile/profil.html.twig', [
            'profileForm' => $form->createView()
        ]);
    }
}
