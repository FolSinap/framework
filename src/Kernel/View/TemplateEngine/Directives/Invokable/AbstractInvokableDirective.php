<?php


namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives\Invokable;

use Closure;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\AbstractDirective;
use Fwt\Framework\Kernel\View\TemplateEngine\Directives\DirectiveRegexBuilder;
use Fwt\Framework\Kernel\View\TemplateEngine\ExpressionParser;

abstract class AbstractInvokableDirective extends AbstractDirective
{
    protected ExpressionParser $parser;

    public function __construct(ExpressionParser $parser)
    {
        $this->parser = $parser;
    }

    abstract public function __invoke(...$args): string;

    public function getRegex(): string
    {
        return DirectiveRegexBuilder::getBuilder()
            ->name($this->getOpeningTag())
            ->useNumbers()
            ->includeForSearch('\'", +-*/!?.%&()[]=>')
            ->useQuotes(false)
            ->setParentheses()
            ->getRegex();
    }

    public function execute(array $matches): string
    {
        $args = explode(',', $matches[1]);

        foreach ($args as $position => $arg) {
            $args[$position] = $this->parser->processExpression($arg);
        }

        return $this->__invoke(...$args);
    }
}