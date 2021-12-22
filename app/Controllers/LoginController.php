<?php

namespace App\Controllers;

use App\Controllers\RequestValidators\RegisterRequestValidator;
use App\Models\User;
use Fwt\Framework\Kernel\Controllers\AbstractController;
use Fwt\Framework\Kernel\Login\PasswordHasher;
use Fwt\Framework\Kernel\Request;
use Fwt\Framework\Kernel\Response\RedirectResponse;
use Fwt\Framework\Kernel\Response\Response;

class LoginController extends AbstractController
{
    public function registrationForm(): Response
    {
        return $this->render('login/register-form.php');
    }

    public function register(RegisterRequestValidator $validator): RedirectResponse
    {
        if (!$validator->validate()) {
            return $this->redirectBack();
        }

        User::create($validator->getBodyData());

        return $this->redirect('/login');
    }

//    public function loginForm(): Response
//    {
//        return $this->render('login/login-form.php');
//    }
//
//    public function login(Request $request, PasswordHasher $hasher): RedirectResponse
//    {
////        $data = $request->getBodyParameters();
////
////        dd(User::login($data));
//
//        return $this->redirect('/login');
//    }
}
