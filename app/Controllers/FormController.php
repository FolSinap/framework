<?php

namespace App\Controllers;

use Fwt\Framework\Kernel\Controllers\AbstractController;
use Fwt\Framework\Kernel\Response\RedirectResponse;
use Fwt\Framework\Kernel\Response\Response;
use Fwt\Framework\Kernel\Request;
use Fwt\Framework\Kernel\Validator\Rules\TypeRule;
use Fwt\Framework\Kernel\Validator\Validator;

class FormController extends AbstractController
{
    public function show(): Response
    {
        return $this->render('form.php');
    }

    public function process(Request $request): RedirectResponse
    {
        $validator = new Validator([
            'email' => [new TypeRule(TypeRule::TYPE_STRING)],
            'password' => [new TypeRule(TypeRule::TYPE_STRING)],
        ]);

        if (!$validator->validateData($request->getBodyParameters())) {
            return $this->redirectBack();
        }

        return $this->redirect('/', ['success' => 'Success!']);
    }
}
