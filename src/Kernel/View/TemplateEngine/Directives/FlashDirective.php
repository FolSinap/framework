<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\Session\Session;

class FlashDirective
{
    public function execute($data): string
    {
        if (!is_string($data)) {
            throw new \Exception('unknown type');
        }

        $session = Session::start();

        if ($session->has($data)) {
            $return = $session->get($data);
            $return = is_array($return) ? implode("\n", $return) : $return;

            $session->unset($data);
        }

        return $return ?? '';
    }
}
