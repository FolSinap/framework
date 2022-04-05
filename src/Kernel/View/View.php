<?php

namespace FW\Kernel\View;

use FW\Kernel\View\TemplateEngine\Templates\Template;
use FW\Kernel\View\TemplateEngine\TemplateFactory;
use FW\Kernel\View\TemplateEngine\TemplateRenderer;

class View
{
    protected Template $template;
    protected TemplateRenderer $renderer;

    public function __construct(Template $template, array $data = [])
    {
        app()->getContainer()->set(VariableContainer::class, VariableContainer::getInstance($data));

        $this->setTemplate($template);
        $this->renderer = new TemplateRenderer();
    }

    public static function create(string $template, array $data = []): self
    {
        $template = (new TemplateFactory())->create($template);

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
