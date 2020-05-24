<?php

namespace App\Security;

use App\Repository\UserRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class MailAuthenticator extends AbstractGuardAuthenticator
{
    private $entityManager;
    private $userRepository;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request)
    {
        return $request->query->has('token');
    }

    public function getCredentials(Request $request)
    {
        return $request->query->get('token');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            return null;
        }

        // Check if user exists with token.
        $user = $this->userRepository->findOneBy(['auth_token' => $credentials]);
        if (null === $user) {
            return null;
        }

        // Check if token is expired.
        if ($user->getAuthTokenExpires() <= Carbon::now()->getTimestamp()) {
            return null;
        }

        // Remove token to enabled on-time-login.
        $user->setAuthTokenExpires(0);
        $user->setAuthToken(null);
        $this->entityManager->flush();

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response('Authentication failed', 404);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // TODO: Implement start() method.// TODO: Implement start() method.
        return new Response('Authentication required', 404);
    }
}