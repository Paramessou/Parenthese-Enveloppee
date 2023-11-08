<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserLoginType;
use App\Form\UserRegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegistrationController extends AbstractController
{
    #[Route('/registration', name: 'main_registrationForm')]
    public function index(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $userPasswordHasher, AuthenticationUtils $authenticationUtils, TokenStorageInterface $tokenStorage, SessionInterface $session): Response
    {
        // code pour formulaire d'inscription
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $user->setRoles('ROLE_USER');
            $user->setGenre('Autre');
            $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPassword()));
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();
            $this->addFlash('success', 'Votre compte a bien été créé');

            // Connecte automatiquement l'utilisateur après son inscription
            $token = new UsernamePasswordToken($user, 'main', []);
            $tokenStorage->setToken($token);
            $session->set('_security_main', serialize($token));

            return $this->redirectToRoute('main_accueil');
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors de la création de votre compte');
        }

        // code pour formulaire de connexion
        $loginUser = new User();
        $loginForm = $this->createForm(UserLoginType::class, $loginUser);
        $loginForm->handleRequest($request);
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $user = $entityManagerInterface->getRepository(User::class)->findOneBy(['email' => $loginUser->getEmail()]);
            if (null === $user) {
                $this->addFlash('error', 'Identifiants incorrects');
            } else {
                if ($userPasswordHasher->isPasswordValid($user, $loginUser->getPassword())) {
                    $token = new UsernamePasswordToken($user, 'main', []);
                    $tokenStorage->setToken($token);
                    $session->set('_security_main', serialize($token));
                    $this->addFlash('success', 'Vous êtes connecté');
                    return $this->redirectToRoute('main_accueil');
                } else {
                    $this->addFlash('error', 'Identifiants incorrects');
                }
            }
        }

        // obtient l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('registration/registration.html.twig', [
            'controller_name' => 'RegistrationController',
            'userRegistrationForm' => $form->createView(),
            'userLoginForm' => $loginForm->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
