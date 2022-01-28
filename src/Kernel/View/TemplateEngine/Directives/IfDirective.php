<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\Exceptions\ExpressionParser\UndefinedKeyException;
use Fwt\Framework\Kernel\Exceptions\View\UnknownArgumentException;
use Fwt\Framework\Kernel\View\TemplateEngine\ExpressionParser;

class IfDirective extends Directive
{
    protected const ELIF = '#elif';
    protected const ELSE = '#else';

    protected ExpressionParser $expressionParser;

    public function __construct(ExpressionParser $expressionParser)
    {
        $this->expressionParser = $expressionParser;
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
        $expression = $matches[1];
        $block = $matches[2];
        $expressions = $this->getElifExpressions($block);
        $blocks = $this->parseBlock($block);

        if ($this->elseExists($block)) {
            $else = array_pop($blocks);
        }

        array_unshift($expressions, $expression);
        $merged = array_combine($expressions, $blocks);

        foreach ($merged as $expression => $block) {
            if ($this->processExpression($expression)) {
                return $block;
            }
        }

        return $else ?? '';
    }

    public function getName(): string
    {
        return 'if';
    }

    protected function processExpression(string $expression): bool
    {
        try {
            return (bool) $this->expressionParser->processExpression($expression);
        } catch (UndefinedKeyException|UnknownArgumentException $exception) {
            return false;
        }
    }

    protected function parseBlock(string $block): array
    {
        $blocks = preg_split($this->getRegexForElif(), $block);

        $lastKey = array_key_last($blocks);
        $elseBlocks = preg_split($this->getRegexForElse(), $blocks[$lastKey]);

        for ($i = 0; $i < count($elseBlocks); $i++) {
            $blocks[$lastKey + $i] = $elseBlocks[$i];
        }

        return $blocks;
    }

    protected function getElifExpressions(string $block): array
    {
        preg_match_all($this->getRegexForElif(), $block, $matches, PREG_OFFSET_CAPTURE + PREG_SET_ORDER);

        $expressions = [];

        foreach ($matches as $match) {
            $expressions[] = $match[1][0];
        }

        return $expressions;
    }

    protected function elseExists(string $block): bool
    {
        return preg_match($this->getRegexForElse(), $block);
    }

    protected function getRegexForElif(): string
    {
        return DirectiveRegexBuilder::getBuilder()
            ->name(self::ELIF)
            ->useQuotes(false)
            ->setParentheses()
            ->includeForSearch('\'&?+*|-/%!><. ')
            ->getRegex();
    }

    protected function getRegexForElse(): string
    {
        return DirectiveRegexBuilder::getBuilder()
            ->name(self::ELSE)
            ->getRegex();
    }
}
