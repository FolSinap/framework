<?php

namespace FW\Kernel;

use FW\Kernel\Routing\Route;

class Request
{
    protected string $path;
    protected string $method;
    protected string $resource;
    protected array $bodyParameters;
    protected array $queryParameters;

    public function __construct()
    {
        if (!defined('STDIN')) {
            $this->initPath();
            $this->initGlobals();
            $this->initMethod();

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
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === Route::POST && array_key_exists('_method', $this->getBodyParameters())) {
            switch ($this->getBodyParameters()['_method']) {
                case Route::PUT:
                    $method = Route::PUT;

                    break;
                case Route::PATCH:
                    $method = Route::PATCH;

                    break;
                case Route::DELETE:
                    $method = Route::DELETE;

                    break;
                default:
                    $method = Route::POST;
            }

            unset($this->bodyParameters['_method']);
        }

        $this->method = $method;
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
