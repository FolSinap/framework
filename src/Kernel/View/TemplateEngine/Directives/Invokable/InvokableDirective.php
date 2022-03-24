<?php


namespace FW\Kernel\View\TemplateEngine\Directives\Invokable;

use FW\Kernel\View\TemplateEngine\Directives\Directive;
use FW\Kernel\View\TemplateEngine\Directives\DirectiveRegexBuilder;
use FW\Kernel\View\TemplateEngine\ExpressionParser;

abstract class InvokableDirective extends Directive
{
    public function __construct(
        protected ExpressionParser $parser
    ) {
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
            ->letEmptyContent()
            ->getRegex();
    }

    public function execute(array $matches): string
    {
        $args = $this->parser->getFunctionArgsFromExpression($matches[1]);

        return $this->__invoke(...$args);
    }
}