<?php

namespace FW\Kernel;

use Dotenv\Dotenv;
use FW\Kernel\Database\Connection;
use FW\Kernel\Database\Database;
use FW\Kernel\Exceptions\Router\InvalidResponseValue;
use FW\Kernel\Middlewares\MiddlewareMapper;
use FW\Kernel\Response\Response;
use FW\Kernel\Routing\Router;
use FW\Kernel\Config\Config;

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
        $pipeline = $this->getRouter()->resolve($this->getRequest());

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
        $this->container[Container::class] = $this->container;

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
        $loader = new FileLoader();

        $loader->load($this->config->get('app.routes.dir'));
        $loader->requireOnceAll();
    }
}
