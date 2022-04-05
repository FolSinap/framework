<?php

namespace FW\Kernel;

use Dotenv\Dotenv;
use FW\Kernel\Database\Connection;
use FW\Kernel\Database\Database;
use FW\Kernel\ErrorHandlers\ConsoleOutputHandler;
use FW\Kernel\ErrorHandlers\ProductionHandler;
use FW\Kernel\Exceptions\Router\InvalidResponseValue;
use FW\Kernel\Middlewares\MiddlewareMapper;
use FW\Kernel\Response\Response;
use FW\Kernel\Routing\Router;
use FW\Kernel\Config\Config;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Whoops\Util\Misc;

class App
{
    public static self $app;
    protected Container $container;
    protected Config $config;
    protected string $env;

    public function __construct(
        protected string $projectDir
    )
    {
        self::$app = $this;

        $this->initEnv();
        $this->initConfig();
        $this->bootContainer();
        $this->initRoutes();
        $this->env = config('app.env');
        $this->initErrorHandler();
    }

    protected function initErrorHandler()
    {
        $whoops = new Run();

        if ($this->env === 'dev') {
            if (Misc::isAjaxRequest()) {
                $handler = new JsonResponseHandler();
                $handler->setJsonApi(true);
            } elseif (Misc::isCommandLine()) {
                $handler = new ConsoleOutputHandler();
            } else {
                $handler = new PrettyPageHandler();
            }
        } else {
            //todo: production handler shows fatal error message
            $handler = new ProductionHandler();
        }

        $whoops->appendHandler($handler);

        $whoops->register();
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

    public function getConfig(string $key = null, bool $throw = true): mixed
    {
        if ($key) {
            return $this->config->get($key, $throw);
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
        $this->config = Config::load();
    }

    protected function initRoutes(): void
    {
        $loader = new FileLoader();

        $loader->load($this->config->get('app.routes.dir'));
        $loader->requireOnceAll();
    }
}
