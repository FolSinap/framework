<?php

namespace App\Controllers;

use App\Controllers\RequestValidators\RegisterRequestValidator;
use App\Models\User;
use FW\Kernel\Controllers\Controller;
use FW\Kernel\Login\Authentication;
use FW\Kernel\Request;
use FW\Kernel\Response\RedirectResponse;
use FW\Kernel\Response\Response;

class LoginController extends Controller
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

    public function loginForm(): Response
    {
        return $this->render('login/login-form.php');
    }

    public function login(Request $request, Authentication $auth): RedirectResponse
    {
        User::login($request->getBodyParameters());

        if ($auth->isAuthenticated()) {
            return $this->redirect('main', ['success' => 'You\'re logged in!']);
        }

        return $this->redirect('/login');
    }

    public function logout(Authentication $auth): RedirectResponse
    {
        if ($auth->isAuthenticated()) {
            $auth->unAuthenticate();
        }

        return $this->redirect('/login');
    }
}
