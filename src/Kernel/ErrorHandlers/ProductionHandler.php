<?php

namespace FW\Kernel\ErrorHandlers;

use FW\Kernel\Exceptions\View\TemplateNotFoundException;
use FW\Kernel\View\TemplateEngine\Templates\BaseTemplate;
use FW\Kernel\View\TemplateEngine\Templates\Template;
use Whoops\Handler\Handler;

class ProductionHandler extends Handler
{
    /**
     * @inheritDoc
     */
    public function handle(): int
    {
        try {
            $view = new Template('errors/_500.html');
        } catch (TemplateNotFoundException) {
            $view = new BaseTemplate(dirname(__DIR__) . '/View/errors/_500.html');
        }

        echo $view->getContent();

        return Handler::QUIT;
    }

    public function contentType(): string
    {
        return 'text/html';
    }
}
