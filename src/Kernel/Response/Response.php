<?php

namespace Fwt\Framework\Kernel\Response;

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
