<?php

namespace FW\Kernel\Response;

use FW\Kernel\Exceptions\View\TemplateNotFoundException;
use FW\Kernel\View\TemplateEngine\Templates\BaseTemplate;
use FW\Kernel\View\TemplateEngine\Templates\Template;
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
        try {
            $template = Template::fromName('errors/_404.tmt.html');
        } catch (TemplateNotFoundException) {
            $template = new Template(dirname(__DIR__) . '/View/errors/_404.html');
        }

        return new self(new View($template), 404);
    }

    public static function unauthorized(): self
    {
        try {
            $template = Template::fromName('errors/_401.tmt.html');
        } catch (TemplateNotFoundException) {
            $template = new Template(dirname(__DIR__) . '/View/errors/_401.html');
        }

        return new self(new View($template), 401);
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
