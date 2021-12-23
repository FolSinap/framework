<?php

namespace Fwt\Framework\Kernel\Routing;

use Closure;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class Route
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const OPTION = 'OPTION';
    public const VERBS = [self::GET, self::POST, self::PUT, self::PATCH, self::OPTION];

    protected string $url;
    protected string $name;
    protected array $verbs;
    protected array $middlewares = [];
    protected Closure $callback;

    public function __construct(string $url, Closure $callback)
    {
        $this->url = $url;
        $this->callback = $callback;
    }

    public function getCallback(): Closure
    {
        return $this->callback;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function addVerb(string $verb): self
    {
        if (!in_array($verb, self::VERBS)) {
            throw new IllegalValueException($verb, self::VERBS);
        }

        $this->verbs[] = $verb;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function name(string $name = null): self
    {
        if ($name) {
            $this->name = $name;
        }

        return $this;
    }

    public function middleware($middlewares): self
    {
        if (!is_array($middlewares) && !is_string($middlewares)) {
            throw new IllegalTypeException($middlewares, ['string', 'array']);
        } elseif (is_string($middlewares)) {
            $middlewares = [$middlewares];
        }

        array_push($this->middlewares, ...$middlewares);

        return $this;
    }
}
