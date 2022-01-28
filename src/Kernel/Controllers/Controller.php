<?php

namespace Fwt\Framework\Kernel\Controllers;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Login\Authentication;
use Fwt\Framework\Kernel\Login\UserModel;
use Fwt\Framework\Kernel\ObjectResolver;
use Fwt\Framework\Kernel\Response\Response;
use Fwt\Framework\Kernel\Response\RedirectResponse;
use Fwt\Framework\Kernel\Routing\Router;
use Fwt\Framework\Kernel\Session\Session;
use Fwt\Framework\Kernel\View\View;

abstract class Controller
{
    protected function render(string $template, array $data = []): Response
    {
        return Response::create(View::create($template, $data));
    }

    protected function redirect(string $url, array $flashMessages = []): RedirectResponse
    {
        $router = Router::getRouter();

        $this->addFlashes($flashMessages);

        if ($router->namedRouteExists($url)) {
            return RedirectResponse::create($router->resolveUrlByName($url));
        }

        return RedirectResponse::create($url);
    }

    protected function redirectBack(): RedirectResponse
    {
        return $this->redirect(App::$app->getRequest()->getResource());
    }

    protected function addFlashes(array $flashMessages = []): void
    {
        foreach ($flashMessages as $key => $message) {
            Session::start()->set($key, $message);
        }
    }

    protected function getUser(string $name = null): ?UserModel
    {
        $auth = App::$app->getContainer()->get(ObjectResolver::class)->resolve(Authentication::class);

        return $auth->getUser($name);
    }
}
