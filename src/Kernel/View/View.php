<?php

namespace Fwt\Framework\Kernel\View;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Exceptions\View\TemplateNotFoundException;
use Fwt\Framework\Kernel\View\TemplateEngine\Template;
use Fwt\Framework\Kernel\View\TemplateEngine\TemplateFactory;

class View
{
//    protected Template $template;
    protected string $template;
    protected array $data;

    protected function __construct(string $template, array $data = [])
    {
//        $template = (new TemplateFactory())->create($template);
        $template = App::$app->getProjectDir() . '/templates/' . $template;

        $this->setTemplate($template);
        $this->data = $data;
    }

    public static function create(string $template, array $data = []): self
    {
        return new self($template, $data);
    }

    public function render(): string
    {
//        $this->template->renderIncludes();
//        dd($this->template->getContent());


        $layout = $this->renderLayouts();
        $content = $this->renderContent();

        return str_replace('{{content}}', $content, $layout);
    }

    public function renderLayouts(): string
    {
        return file_get_contents(App::$app->getProjectDir() . '/templates/layout/main.php');
    }

    public function renderContent(): string
    {
        return file_get_contents($this->template);
    }

    public function __toString(): string
    {
        return $this->render();
    }

    protected function setTemplate(string $template): void
    {
        $this->template = $template;
    }

//    protected function setTemplate(Template $template): void
//    {
//        $this->template = $template;
//    }
}