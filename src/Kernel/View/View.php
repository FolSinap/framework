<?php

namespace Fwt\Framework\Kernel\View;

use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;
use Fwt\Framework\Kernel\View\TemplateEngine\TemplateFactory;
use Fwt\Framework\Kernel\View\TemplateEngine\TemplateRenderer;

class View
{
    protected Template $template;
    protected TemplateRenderer $renderer;

    protected function __construct(string $template, array $data = [])
    {
        $template = (new TemplateFactory())->create($template, $data);

        $this->setTemplate($template);
        $this->renderer = new TemplateRenderer();
    }

    public static function create(string $template, array $data = []): self
    {
        return new self($template, $data);
    }

    public function render(): string
    {
        return $this->renderer->render($this->template);
    }

    public function __toString(): string
    {
        return $this->render();
    }

    protected function setTemplate(Template $template): void
    {
        $this->template = $template;
    }
}
