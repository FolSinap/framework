<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives\Invokable;

use Fwt\Framework\Kernel\Csrf\CsrfValidator as CsrfValidator;

class CsrfDirective extends InvokableDirective
{
    public function getName(): string
    {
        return 'csrf';
    }

    public function __invoke(...$args): string
    {
        if (func_num_args() > 0) {
            //todo: exception
            throw new \Exception('scrf directive doesn\'t take any arguments.');
        }

        $token = CsrfValidator::getValidator()->generate();
        $name = CsrfValidator::TOKEN_KEY;

        //todo: is calling #flash() better than do it yourself???
        return "<div style='color: red'>#flash('errors.$name')</div>"
            . "<input type='hidden' value='$token' name='$name'>";
    }
}
