<?php

namespace Fwt\Framework\Kernel\Controllers;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Response\Response;
use Fwt\Framework\Kernel\Router;
use Fwt\Framework\Kernel\Response\RedirectResponse;
use Fwt\Framework\Kernel\View\View;

abstract class AbstractController
{
    protected function render(string $template, array $data = []): Response
    {
        return Response::create(View::create($template, $data));
    }

    protected function redirect(string $url): RedirectResponse
    {
        $router = Router::getRouter();

        if ($router->namedRouteExists($url)) {
            return RedirectResponse::create($router->resolveUrlByName($url));
        }

        return RedirectResponse::create($url);
    }

    protected function redirectBack(): RedirectResponse
    {
        return $this->redirect(App::$app->getRequest()->getResource());
    }
}
