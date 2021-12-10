<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Exceptions\View\TemplateNotFoundException;

class Template
{
    protected string $path;
    protected string $template;
    protected string $content;
    /**
     * @var Template[] $includes
     */
    protected array $includes = [];
    protected self $parent;
    protected TemplateFactory $factory;

    public function __construct(string $template)
    {
        $this->factory = new TemplateFactory();
        $this->template = $template;
        $this->setPath(App::$app->getProjectDir() . '/templates/' . $template);

        $this->loadContent();
        $this->initIncludes();
    }

    public function include(self $template): self
    {
        $this->includes[$template->getTemplate()] = $template;

        return $this;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }

    public function inherit(self $template): self
    {
        $this->parent = $template;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function renderIncludes(): void
    {
        $this->content = preg_replace_callback('/#include\([\'"]([a-zA-Z-_\.\/]+)[\'"]\)/',
            function ($matches) {
                return $this->getIncludes()[$matches[1]]->getContent();
            }, $this->content);
    }

    protected function loadContent(): void
    {
        $this->content = file_get_contents($this->path);
    }

    protected function initIncludes(): void
    {
        preg_match_all('/#include\([\'"]([a-zA-Z-_\.\/]+)[\'"]\)/',
            $this->content, $matches, PREG_OFFSET_CAPTURE + PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $this->include($this->factory->create($match[1][0]));
        }
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
