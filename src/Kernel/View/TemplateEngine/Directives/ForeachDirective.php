<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\Exceptions\ExpressionParser\ForeachExpressionException;
use Fwt\Framework\Kernel\View\TemplateEngine\ExpressionParser;

class ForeachDirective extends AbstractDirective
{
    protected ExpressionParser $parser;

    public function __construct(ExpressionParser $parser)
    {
        $this->parser = $parser;
    }

    public function getRegex(): string
    {
        return DirectiveRegexBuilder::getBuilder()
            ->name($this->getOpeningTag())
            ->useQuotes(false)
            ->setParentheses()
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
            $block = preg_replace_callback("/\{\{.*($valueTemplate).*\}\}/", function ($matches) use($arrayComponent, $key) {
                return str_replace($matches[1], $arrayComponent . "[$key]", $matches[0]);
            }, $matches[2]);

            if (isset($keyTemplate)) {
                $block = preg_replace("/\{\{$keyTemplate\}\}/", $key, $block);
            }

            $return .= $block;
        }

        return $return;
    }

    public function getName(): string
    {
        return 'foreach';
    }
}
