<?php

namespace FW\Kernel\View\TemplateEngine\Templates;

use FW\Kernel\Exceptions\View\TemplateNotFoundException;

class BaseTemplate
{
    protected string $path;
    protected string $content;

    public function __construct(string $path)
    {
        $this->setPath($path);
        $this->loadContent();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    protected function loadContent(): void
    {
        $this->content = file_get_contents($this->path);
    }

    protected function setPath(string $path): void
    {
        if (file_exists($path)) {
            $this->path = $path;
        } else {
            throw new TemplateNotFoundException($path);
        }
    }
}
