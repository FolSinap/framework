<?php

namespace FW\Kernel\Response;

use FW\Kernel\View\View;

class Response
{
    protected int $code;
    protected string $content;

    public function __construct(string $content = '', int $code = 200)
    {
        $this->content = $content;
        $this->setCode($code);
    }

    public static function create(string $content = '', int $code = 200): self
    {
        return new self($content, $code);
    }

    public static function notFound(): self
    {
        //todo: put it in config
        return new self(View::create('errors/_404.html'), 404);
    }

    public static function unauthorized(): self
    {
        //todo: put it in config
        return new self(View::create('errors/_401.html'), 401);
    }

    public function setCode(int $code): self
    {
        $this->code = $code;
        http_response_code($code);

        return $this;
    }

    public function send(): void
    {
        echo $this->content;
    }
}
