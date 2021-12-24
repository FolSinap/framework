<?php

namespace Fwt\Framework\Kernel;

use Dotenv\Dotenv;
use Fwt\Framework\Kernel\Database\Connection;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Exceptions\Router\InvalidResponseValue;
use Fwt\Framework\Kernel\Middlewares\MiddlewareMapper;
use Fwt\Framework\Kernel\Response\Response;
use Fwt\Framework\Kernel\Routing\Router;

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

    public function getConfig(): Config
    {
        return $this->config;
    }

    protected function bootContainer(): void
    {
        $this->container = Container::getInstance();

        $this->container[Request::class] = new Request();
        $resolver = $this->container[ObjectResolver::class] = new ObjectResolver();
        $this->container[Router::class] = Router::getRouter($resolver);
        $this->container[MiddlewareMapper::class] = new MiddlewareMapper($resolver);

        $this->container[Connection::class] = new Connection(
            getenv('DB'),
            getenv('DB_HOST'),
            getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASSWORD')
        );
        $this->container[Database::class] = new Database($this->container[Connection::class]);
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
