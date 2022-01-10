<?php


namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives\Invokable;

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
        $args = $this->parser->getFunctionArgsFromExpression($matches[1]);

        return $this->__invoke(...$args);
    }
}