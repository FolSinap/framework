<?php

namespace Fwt\Framework\Kernel\Controllers;

use Fwt\Framework\Kernel\Response\RedirectResponse;
use Fwt\Framework\Kernel\Response\Response;
use Fwt\Framework\Kernel\Request;
use Fwt\Framework\Kernel\Session\Session;
use Fwt\Framework\Kernel\Validator\Rules\TypeRule;
use Fwt\Framework\Kernel\Validator\Validator;

class FormController extends AbstractController
{
    public function show(): Response
    {
        return $this->render('form.php', ['arg' => 'value']);
    }

    public function process(Request $request): RedirectResponse
    {
        $validator = new Validator([
            'email' => [new TypeRule(TypeRule::TYPE_STRING)],
            'password' => [new TypeRule(TypeRule::TYPE_STRING)],
        ]);

        $errors = $validator->validate($request->getBodyParameters());

        if ($errors) {
            Session::start()->set('errors', $errors);

            return $this->redirectBack();
        }

        return $this->redirect('form_show');
    }
}
