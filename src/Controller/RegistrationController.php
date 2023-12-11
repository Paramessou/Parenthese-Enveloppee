<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User(); // Crée une nouvelle instance de User
        $user->setRoles(['ROLE_USER']); // Définit le rôle de l'utilisateur
        $form = $this->createForm(RegistrationFormType::class, $user); // Crée un formulaire d'inscription
        $form->handleRequest($request); // Gère la requête

        if ($form->isSubmitted() && $form->isValid()) { // Si le formulaire est soumis et valide
            // Chiffre le mot de passe
            $user->setPassword( // Définit le mot de passe de l'utilisateur
                $userPasswordHasher->hashPassword( // Chiffre le mot de passe
                    $user,
                    $form->get('plainPassword')->getData() // Mot de passe en clair
                )
            );

            $entityManager->persist($user); // Persiste l'utilisateur
            $entityManager->flush(); // Enregistre l'utilisateur

            // Génère un url et un email d'enregistrement pour l'utilisateur
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('parenthese.enveloppee@gmail.com', 'Parenthèse Enveloppée'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse email s\'il vous plaît.')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser( // Authentifie l'utilisateur
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [ // Affiche le formulaire d'inscription
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // vérifie le lien de confirmation de l'email, définit User::isVerified=true et persiste
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // Change la redirection en cas de succès et gère ou supprime le message flash dans mes templates
        $this->addFlash('success', 'Votre adresse mail a bien été vérifiée');

        return $this->redirectToRoute('app_register');
    }
}
