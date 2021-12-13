<?php

namespace Fwt\Framework\Kernel\Exceptions\View;

use Fwt\Framework\Kernel\View\TemplateEngine\Templates\Template;
use LogicException;
use Throwable;

class InheritException extends LogicException
{
    protected function __construct($message, $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function inheritMoreThanOnce(Template $template, $code = 500, Throwable $previous = null): self
    {
        $message = $template->getTemplate() . ' is using \'inherit\' directive more than once.';

        return new self($message, $code, $previous);
    }

    public static function invalidContentBlocksCount(string $name, int $count, $code = 500, Throwable $previous = null): self
    {
        $message = $name . ' is used ' . $count . ' times, should be exactly 1.';

        return new self($message, $code, $previous);
    }

    public static function invalidEndBlocksCount($code = 500, Throwable $previous = null): self
    {
        return new self('Invalid endblock directives count', $code, $previous);
    }
}
