<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Exceptions\Config\ValueIsNotConfiguredException;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\Login\Authentication;

class IfAuthDirective implements Directive
{
    protected Authentication $auth;

    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    public function getRegex(): string
    {
        return DirectiveRegexBuilder::getBuilder()
            ->name($this->getName())
            ->setParentheses()
            ->useQuotes(false)
            ->letEmptyContent()
            ->setClosingTag($this->getClosingTag())
            ->getRegex();
    }

    public function execute(array $matches): string
    {
        if (!$this->auth->isAuthenticated()) {
            return '';
        }

        $name = $matches[1] ?: 'main';

        $classes = App::$app->getConfig('auth.user_classes');

        if (!array_key_exists($name, $classes)) {
            throw new IllegalValueException("$name", array_keys($classes));
        }

        if (!$this->auth->isAuthenticatedAs($name)) {
            return '';
        }

        return $matches[2];
    }

    public function getName(): string
    {
        return '#ifauth';
    }

    public function getClosingTag(): string
    {
        return '#endauth';
    }
}
