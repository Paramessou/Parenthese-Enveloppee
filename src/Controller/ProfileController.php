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
        $user = $this->getUser(); // get the current user
        $form = $this->createForm(UserEditType::class, $user); // create form

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get data from the form
            $data = $form->getData();

            // check if the user has submitted a new password
            if ($data->getPlainPassword()) {
                // hash the plain password and set it for the user
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $data->getPlainPassword()
                    )
                );
            }

            // if ($form->isSubmitted() && $form->isValid()) {
            //     // Si l'utilisateur a fourni un nouveau mot de passe, cela le hache et le définit pour l'utilisateur
            //     if ($user->getPlainPassword()) {
            //         $user->setPassword(
            //             $passwordHasher->hashPassword(
            //                 $user,
            //                 $user->getPlainPassword()
            //             )
            //         );
            //     }
            // sauvergarde l'utilisateur dans la BDD
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('profile_show');
        }
        return $this->render('profile/profil.html.twig', [
            'controller_name' => 'ProfileController',
            'profileForm' => $form->createView()
        ]);
    }
}
