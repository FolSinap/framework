<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

class TemplateRenderer
{
    public const INCLUDE = 'include';
    public const INHERIT = 'inherit';

    public function render(Template $template)
    {
        $content = $template->getContent();
    }

    protected function renderIncludes(Template $template)
    {
        $includedTemplate = $template->nextInclude();

//        $this
        $content = $template->getContent();
    }

    protected function parse(string $content)
    {
        $this->renderIncludes($content);
    }
}
