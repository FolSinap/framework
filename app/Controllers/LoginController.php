<?php

namespace App\Controllers;

use App\Controllers\RequestValidators\RegisterRequestValidator;
use Fwt\Framework\Kernel\Controllers\AbstractController;
use Fwt\Framework\Kernel\Request;
use Fwt\Framework\Kernel\Response\Response;
use Fwt\Framework\Kernel\Validator\Rules\TypeRule;
use Fwt\Framework\Kernel\Validator\Validator;

class LoginController extends AbstractController
{
    public function registrationForm(): Response
    {
        return $this->render('login/register-form.php');
    }

    public function register(RegisterRequestValidator $validator): Response
    {
        dd($validator->validate());
    }
}
