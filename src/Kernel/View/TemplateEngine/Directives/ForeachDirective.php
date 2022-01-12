<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\Exceptions\ExpressionParser\ForeachExpressionException;
use Fwt\Framework\Kernel\View\TemplateEngine\ExpressionParser;
use Fwt\Framework\Kernel\View\TemplateEngine\TemplateRenderer;
use Fwt\Framework\Kernel\View\VariableContainer;

class ForeachDirective extends AbstractDirective
{
    protected ExpressionParser $parser;
    protected VariableContainer $container;
    protected TemplateRenderer $renderer;

    public function __construct(ExpressionParser $parser, VariableContainer $container, TemplateRenderer $renderer)
    {
        $this->parser = $parser;
        $this->container = $container;
        $this->renderer = $renderer;
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
            throw ForeachExpressionException::mustContainIn();
        }

        $keyValue = str_replace(' ', '', $components[0]);
        $keyValue = explode(',', $keyValue);

        if (count($keyValue) === 1) {
            $valueTemplate = $keyValue[0];
        } elseif (count($keyValue) === 2) {
            $keyTemplate = $keyValue[0];
            $valueTemplate = $keyValue[1];
        } else {
            throw ForeachExpressionException::tooManyVarsInDefinition();
        }

        $arrayComponent = $components[1];
        $array = $this->parser->getVariable($arrayComponent);

        if (!is_iterable($array)) {
            throw ForeachExpressionException::notIterable();
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
