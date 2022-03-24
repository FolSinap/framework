<?php

namespace FW\Kernel\View\TemplateEngine\Directives;

use FW\Kernel\Exceptions\ExpressionParser\ForeachExpressionException;
use FW\Kernel\View\TemplateEngine\ExpressionParser;
use FW\Kernel\View\TemplateEngine\TemplateRenderer;
use FW\Kernel\View\VariableContainer;

class ForeachDirective extends Directive
{
    public function __construct(
        protected ExpressionParser $parser,
        protected VariableContainer $container,
        protected TemplateRenderer $renderer
    ) {
    }

    public function getRegex(): string
    {
        return DirectiveRegexBuilder::getBuilder()
            ->name($this->getOpeningTag())
            ->useQuotes(false)
            ->setParentheses()
            ->setIsGreedy()
            ->includeForSearch('.[] ')
            ->setClosingTag($this->getClosingTag())
            ->getRegex();
    }

    public function execute(array $matches): string
    {
        $components = explode(' in ', $matches[1]);

        if (count($components) !== 2) {
            throw new ForeachExpressionException("Foreach must contain one 'in' statement");
        }

        $keyValue = str_replace(' ', '', $components[0]);
        $keyValue = explode(',', $keyValue);

        if (count($keyValue) === 1) {
            $valueTemplate = $keyValue[0];
        } elseif (count($keyValue) === 2) {
            $keyTemplate = $keyValue[0];
            $valueTemplate = $keyValue[1];
        } else {
            throw new ForeachExpressionException("Syntax error in foreach expression: expression should be 'key, value' or 'value'");
        }

        $arrayComponent = $components[1];
        $array = $this->parser->getVariable($arrayComponent);

        if (!is_iterable($array)) {
            throw new ForeachExpressionException("Variable in foreach expression must be iterable");
        }

        $return = '';

        foreach ($array as $key => $value) {
            $this->container->set($valueTemplate, $value);

            if (isset($keyTemplate)) {
                $this->container->set($keyTemplate, $key);
            }

            $block = $this->renderer->executeDirectives($matches[2]);

            $return .= $block;
        }

        return $return;
    }

    public function getName(): string
    {
        return 'foreach';
    }
}
