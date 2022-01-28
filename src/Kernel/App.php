<?php

namespace Fwt\Framework\Kernel;

use Dotenv\Dotenv;
use Fwt\Framework\Kernel\Database\Connection;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Exceptions\Router\InvalidResponseValue;
use Fwt\Framework\Kernel\Middlewares\MiddlewareMapper;
use Fwt\Framework\Kernel\Response\Response;
use Fwt\Framework\Kernel\Routing\Router;
use Fwt\Framework\Kernel\Config\Config;

class App
{
    public static self $app;
    protected string $projectDir;
    protected Container $container;
    protected Config $config;

    public function __construct(string $projectDir)
    {
        self::$app = $this;
        $this->projectDir = $projectDir;

        $this->initEnv();
        $this->initConfig();
        $this->bootContainer();
        $this->initRoutes();
    }

    public function run(): void
    {
        $pipeline = $this->getRouter()->resolve($this->getRequest()->getPath(), $this->getRequest()->getMethod());

        $response = $pipeline->send($this->getRequest())->go();

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

    public function getConfig(string $key = null, $default = null)
    {
        if ($key) {
            return $this->config->get($key, $default);
        }

        return $this->config;
    }

    protected function bootContainer(): void
    {
        $this->container = Container::getInstance();

        $this->container[Request::class] = new Request();
        $resolver = $this->container[ObjectResolver::class] = new ObjectResolver();
        $this->container[Router::class] = Router::getRouter();
        $this->container[MiddlewareMapper::class] = $resolver->resolve(MiddlewareMapper::class);
        $this->container[Connection::class] = $resolver->resolve(Connection::class);
        $this->container[Database::class] = $resolver->resolve(Database::class);
    }

    protected function initEnv(): void
    {
        $env = Dotenv::createUnsafeImmutable($this->projectDir);
        $env->load();
    }

    protected function initConfig(): void
    {
        $this->config = Config::getInstance();
    }

    protected function initRoutes(): void
    {
        $routesDir = $this->config->get('app.routes.dir');
        $files = $this->config->get('app.routes.files');

        foreach ($files as $file) {
            require_once "$routesDir/$file";
        }
    }
}
