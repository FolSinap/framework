<?php

namespace FW\Kernel\Controllers;

use FW\Kernel\App;
use FW\Kernel\Login\Authentication;
use FW\Kernel\Login\UserModel;
use FW\Kernel\ObjectResolver;
use FW\Kernel\Response\Response;
use FW\Kernel\Response\RedirectResponse;
use FW\Kernel\Routing\Router;
use FW\Kernel\Storage\Session;
use FW\Kernel\View\View;

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
        $auth = container(ObjectResolver::class)->resolve(Authentication::class);

        return $auth->getUser($name);
    }
}
