<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfileController extends AbstractController
{
    #[Route('/profil/edit', name: 'profile_edit')]
    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $managerRegistry): Response
    {
        $user = $this->getUser(); // récupère l'utilisateur connecté
        $form = $this->createForm(UserEditType::class, $user); // crée le formulaire avec l'utilisateur connecté

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // vérifie si l'utilisateur a fourni un nouveau mot de passe
            if ($user instanceof User) {
                // vérifie si l'utilisateur a fourni un nouveau mot de passe
                $plainPassword = $user->getPlainPassword();
                if ($plainPassword !== null) {
                    // hash le nouveau mot de passe
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

                    // vérifie si le mot de passe haché est valide
                    if ($hashedPassword) {
                        //définit le nouveau mot de passe pour l'utilisateur
                        $user->setPassword($hashedPassword);
                    }
                }
            }

            // sauvergarde l'utilisateur dans la BDD
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a bien été modifié');

            return $this->redirectToRoute('profile_edit');
        }
        return $this->render('profile/profil.html.twig', [
            'profileForm' => $form->createView()
        ]);
    }

    #[Route('/profil/delete', name: 'profile_delete', methods: ['DELETE'])]
    public function delete(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $user = $this->getUser(); // récupère l'utilisateur connecté

        if ($user instanceof User) {
            $userId = $user->getId(); // récupère l'ID de l'utilisateur connecté
            $userToDelete = $managerRegistry->getRepository(User::class)->find($userId);

            // Vérifie le token CSRF
            if ($this->isCsrfTokenValid('delete_user', $request->headers->get('X-CSRF-TOKEN'))) {
                if ($userToDelete && $request->isMethod('DELETE')) { // Vérifie si la méthode de la requête est DELETE
                    $entityManager = $managerRegistry->getManager();
                    $entityManager->remove($userToDelete);
                    $entityManager->flush();

                    return new Response('', Response::HTTP_NO_CONTENT);
                }
            }
        }

        return new Response('', Response::HTTP_NOT_FOUND);
    }
}
