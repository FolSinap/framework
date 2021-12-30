<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\Login\Authentication;

class AuthDirective extends AbstractDirective
{
    protected Authentication $auth;

    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    public function getRegex(): string
    {
        return DirectiveRegexBuilder::getBuilder()
            ->name($this->getOpeningTag())
            ->setParentheses()
            ->useQuotes(false)
            ->letEmptyContent()
            ->setClosingTag($this->getClosingTag())
            ->getRegex();
    }

    public function execute(array $matches): string
    {
        if ($this->checkAuth($matches)) {
            return $matches[2];
        }

        return '';
    }

    public function getName(): string
    {
        return 'auth';
    }

    protected function checkAuth(array $matches): bool
    {
        if (!$this->auth->isAuthenticated()) {
            return false;
        }

        $name = $matches[1] ?: 'main';

        $classes = App::$app->getConfig('auth.user_classes');

        IllegalValueException::checkValue($name, array_keys($classes));

        if (!$this->auth->isAuthenticatedAs($name)) {
            return false;
        }

        return true;
    }
}
