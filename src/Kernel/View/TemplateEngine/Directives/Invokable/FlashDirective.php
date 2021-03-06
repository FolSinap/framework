<?php

namespace FW\Kernel\View\TemplateEngine\Directives\Invokable;

use FW\Kernel\Storage\Session;

class FlashDirective extends InvokableDirective
{
    public function __invoke(...$args): string
    {
        return $this->getFromSession(...$args);
    }

    public function getName(): string
    {
        return 'flash';
    }

    protected function getFromSession(string $key): string
    {
        $session = Session::start();

        if ($session->has($key)) {
            $return = $session->get($key);
            $return = is_array($return) ? implode("<br>", $return) : $return;

            $session->unset($key);
        }

        return $return ?? '';
    }
}
