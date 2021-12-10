<?php

namespace Fwt\Framework\Kernel\Response;

class RedirectResponse extends Response
{
    protected string $url;

    public function __construct(string $url = '/', int $code = 301)
    {
        $this->url = $url;

        parent::__construct($code);
    }

    public static function create(string $url = '/', int $code = 301): self
    {
        return new self($url, $code);
    }

    public function send(): void
    {
        header("Location: $this->url");
    }
}
