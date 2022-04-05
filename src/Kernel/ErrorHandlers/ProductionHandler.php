<?php

namespace FW\Kernel\ErrorHandlers;

use FW\Kernel\Exceptions\View\TemplateNotFoundException;
use FW\Kernel\View\TemplateEngine\Templates\BaseTemplate;
use FW\Kernel\View\TemplateEngine\Templates\Template;
use FW\Kernel\View\View;
use Whoops\Handler\Handler;

class ProductionHandler extends Handler
{
    /**
     * @inheritDoc
     */
    public function handle(): int
    {
        $error = error_get_last();

        if (is_null($error) || $error['type'] !== E_ERROR) {
            return Handler::DONE;
        }

        try {
            $template = new Template('errors/_500.html');
        } catch (TemplateNotFoundException) {
            try {
                $template = new Template('errors/_500.php');
            } catch (TemplateNotFoundException) {
                $template = new BaseTemplate(dirname(__DIR__) . '/View/errors/_500.html');
            }
        }

        $view = new View($template);
        echo $view->render();

        return Handler::QUIT;
    }

    public function contentType(): string
    {
        return 'text/html';
    }
}
