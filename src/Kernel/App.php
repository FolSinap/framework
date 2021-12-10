<?php

namespace Fwt\Framework\Kernel;

use Fwt\Framework\Kernel\Exceptions\Router\InvalidResponseValue;
use Fwt\Framework\Kernel\Response\Response;

class App
{
    public static self $app;
    protected string $projectDir;
    protected Container $container;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;

        $this->bootContainer();

        self::$app = $this;
    }

    public function run(): void
    {
        $callback = $this->getRouter()->resolve($this->getRequest()->getPath(), $this->getRequest()->getMethod());

        $response = $callback();

        if (!$response instanceof Response) {
            throw new InvalidResponseValue($response);
        }

        $response->send();
    }

    public function getRouter(): Router
    {
        return $this->container[Router::class];
    }

    public function getRequest(): Request
    {
        return $this->container[Request::class];
    }

    public function getProjectDir(): string
    {
        return $this->projectDir;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    protected function bootContainer(): void
    {
        $this->container = Container::getInstance();

        $this->container[Request::class] = new Request();
        $resolver = $this->container[ObjectResolver::class] = new ObjectResolver();
        $this->container[Router::class] = Router::getRouter($resolver);
    }
}
