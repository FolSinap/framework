<?php

namespace Fwt\Framework\Kernel\Routing;

use BadMethodCallException;
use Closure;
use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\ObjectResolver;

class Route
{
    public const GET = 'GET';
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const PATCH = 'PATCH';
    public const OPTIONS = 'OPTIONS';
    public const DELETE = 'DELETE';
    public const VERBS = [self::GET, self::POST, self::PUT, self::PATCH, self::OPTIONS, self::DELETE];

    protected string $url;
    protected string $name;
    protected array $verbs;
    protected array $middlewares = [];
    protected array $wildcards = [];
    protected ObjectResolver $resolver;
    protected $callback;

    public function __construct(string $url, $callback)
    {
        $this->url = $url;
        $this->callback = $callback;
        $this->resolver = App::$app->getContainer()->get(ObjectResolver::class);
    }

    public function match(string $url, string $verb): bool
    {
        return $this->matchVerb($verb) && $this->matchUrl($url);
    }

    public function resolveCallback(): Closure
    {
        $callback = $this->callback;

        if (is_array($callback)) {
            if (!method_exists($callback[0], $callback[1])) {
                throw new BadMethodCallException("$callback[1] doesn't exist in $callback[0]", 500);
            }

            $controller = $this->resolver->resolve($callback[0]);
            $args = $this->resolver->resolveDependencies($callback[0], $callback[1], $this->wildcards);

            return Closure::bind(function () use ($callback, $args) {
                return $this->{$callback[1]}(...$args);
            }, $controller, $callback[0]);
        }

        return Closure::fromCallable($callback);
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

    protected function matchVerb(string $verb): bool
    {
        return in_array($verb, $this->verbs);
    }

    protected function matchUrl(string $url): bool
    {
        if ($url === $this->url) {
            return true;
        }

        $parsedSelf = explode('/', $this->url);
        $parsedMatched = explode('/', $url);

        if (count($parsedSelf) !== count($parsedMatched)) {
            return false;
        }

        foreach ($parsedSelf as $position => $part) {
            if (($part === '' && $position === 0) || ($part === $parsedMatched[$position])) {
                continue;
            } elseif (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $part = rtrim(ltrim($part, '{'), '}');
                $this->wildcards[$part] = $parsedMatched[$position];

                continue;
            }

            return false;
        }

        return true;
    }
}
