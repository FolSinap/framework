<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives\Invokable;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\Routing\Route;

class MethodDirective extends InvokableDirective
{
    public function __invoke(...$args): string
    {
        return $this->renderMethodToken(...$args);
    }

    public function getName(): string
    {
        return 'method';
    }

    protected function renderMethodToken(string $method): string
    {
        $method = strtoupper($method);

        IllegalValueException::checkValue($method, Route::VERBS);

        return '<input type="hidden" name="_method" value="' . $method . '">';
    }
}
