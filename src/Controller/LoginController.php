<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class LoginController
{
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $user = User::where('email', $request->email)
            ->first();

        if ($user === null) {
            return $this->sendFailedLoginResponse($request);
        }

        $token = bin2hex(random_bytes(16));
        $user->auth_token = $token;
        $user->auth_token_expires = Carbon::now()
            ->addMinutes(10);
        $user->save();

        Mail::to($user->email)
            ->send(new LoginVerify($token, $user->email));

        return view('auth.loginVerify');

    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string'
        ]);
    }

    public function verify(Request $request) {
        $token = $request->get('authToken');
        $email = $request->get('email');

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
