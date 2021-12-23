<?php

namespace Fwt\Framework\Kernel;

use Fwt\Framework\Kernel\Session\Session;

class Request
{
    protected string $path;
    protected string $method;
    protected string $resource;
    protected array $bodyParameters;
    protected array $queryParameters;
    protected Session $session;

    public function __construct()
    {
        if (!defined('STDIN')) {
            $this->initPath();
            $this->initMethod();
            $this->initGlobals();
            $this->startSession();

            $this->resource = $_SERVER['HTTP_REFERER'] ?? '/';
        }
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getBodyParameters(): array
    {
        return $this->bodyParameters;
    }

    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }

    protected function initPath(): void
    {
        $this->path = $_SERVER['PATH_INFO'] ?? '/';
    }

    protected function initMethod(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    protected function startSession(): void
    {
        $this->session = Session::start();
    }

    protected function initGlobals(): void
    {
        $this->bodyParameters = $this->normalizeQueryValues($_POST);
        $this->queryParameters = $this->normalizeQueryValues($_GET);
    }

    protected function normalizeQueryValues(array $values): array
    {
        foreach ($values as $key => $value) {
            if ('' === $value) {
                $values[$key] = null;
            }
        }

        return $values;
    }
}