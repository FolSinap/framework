<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine;

use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;

class TemplateRenderer
{
    public function render(Template $template): string
    {
        $template->renderIncludes();
        $parent = $template->getParent();

        if ($parent) {
            $parent->renderIncludes();
            $parent->renderBlocks();
            $template = $parent;
        }

        return $template->getContent();
    }
}
