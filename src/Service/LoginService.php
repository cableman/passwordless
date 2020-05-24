<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class LoginService
{
    private $userRepository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function addToken(User $user) {
        $token = bin2hex(random_bytes(16));
        $expire = Carbon::now()
            ->addMinutes(10)
            ->getTimestamp();

        $user->setAuthToken($token);
        $user->setAuthTokenExpires($expire);
    }

    public function verify(string $token, string $mail) {

        if ($token == null || $email == null) {
            return $this->sendFailedLoginResponse($request);
        }

        $user = User::where([
            ['auth_token', '=', $token],
            ['email', '=', $email]
        ])->first();

        if (!$user) {
            return $this->sendFailedLoginResponse($request);
        }

        $authTokenExpires = $user->auth_token_expire;
        $user->auth_token = null;
        $user->auth_token_expire = null;
        $user->save();

        if ($authTokenExpires <= \Carbon\Carbon::now()) {
            return $this->sendFailedLoginResponse($request);
        }

        Auth::login($user);
        return $this->sendLoginResponse($request);
    }
}
