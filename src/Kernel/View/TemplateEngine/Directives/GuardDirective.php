<?php

namespace FW\Kernel\View\TemplateEngine\Directives;

use FW\Kernel\Exceptions\Guards\GuardDefinitionException;
use FW\Kernel\Guards\GuardMapper;
use FW\Kernel\View\TemplateEngine\ExpressionParser;

class GuardDirective extends Directive
{
    public function __construct(
        protected GuardMapper $mapper,
        protected ExpressionParser $parser
    ) {
    }

    public function getRegex(): string
    {
        return DirectiveRegexBuilder::getBuilder()
            ->name($this->getOpeningTag())
            ->useQuotes(false)
            ->setParentheses()
            ->includeForSearch('\'&?+*|-/%!><.= ')
            ->setClosingTag($this->getClosingTag())
            ->getRegex();
    }

    public function execute(array $matches): string
    {
        $args = $this->parser->getFunctionArgsFromExpression($matches[1]);

        $guard = array_shift($args);

        //todo: reminds FW\Kernel\Routing\Route::guard(), fix this
        if (!str_contains($guard, ':')) {
            throw new GuardDefinitionException("Wrong definition - '$guard'. Guard definition must contain : character.");
        } elseif (substr_count($guard, ':') > 1) {
            throw new GuardDefinitionException("Wrong definition - '$guard'. Too many : characters.");
        }

        [$guardName, $method] = explode(':', $guard);

        $guard = $this->mapper->map($guardName);

        if ($guard->$method(...$args)) {
            return $matches[2];
        }

        return '';
    }

    public function getName(): string
    {
        return 'guard';
    }
}
