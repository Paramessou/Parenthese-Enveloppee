<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait; // Permet de rediriger vers la page précédente après connexion

    public const LOGIN_ROUTE = 'app_login'; // Nom de la route de connexion

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport // Je crée une méthode pour authentifier l'utilisateur
    {
        $email = $request->request->get('email', ''); // Je récupère l'email saisi dans le formulaire

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email); // Je stocke l'email dans la session

        return new Passport( // Je retourne un objet Passport
            new UserBadge($email), // Je passe l'email à UserBadge
            new PasswordCredentials($request->request->get('password', '')), // Je passe le mot de passe à PasswordCredentials
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')), // Je passe le token CSRF à CsrfTokenBadge
                new RememberMeBadge(), // Je passe le badge RememberMeBadge
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response // Je crée une méthode pour rediriger après connexion
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) { // Si la session contient une route cible
            return new RedirectResponse($targetPath); // Je redirige vers la route cible
        }

        return new RedirectResponse($this->urlGenerator->generate('main_accueil')); // Sinon je redirige vers la page d'accueil
    }

    private function getTargetPath(SessionInterface $session, string $firewallName): ?string // Je crée une méthode pour récupérer la route cible
    {
        return $session->get('_security.' . $firewallName . '.target_path'); // Je récupère la route cible
    }

    protected function getLoginUrl(Request $request): string // Je crée une méthode pour rediriger vers la page de connexion
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE); // Je retourne la route de connexion
    }
}
