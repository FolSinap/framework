<?php

namespace Fwt\Framework\Kernel\View\TemplateEngine\Directives;

use Fwt\Framework\Kernel\Session\Session;
use Fwt\Framework\Kernel\View\TemplateEngine\TemplateRegexBuilder;

class FlashDirective extends AbstractDirective
{
    public function execute(array $matches): string
    {
        $key = $matches[1];
        $session = Session::start();

        if ($session->has($key)) {
            $return = $session->get($key);
            $return = is_array($return) ? implode("<br>", $return) : $return;

            $session->unset($key);
        }

        return $return ?? '';
    }

    public function getRegex(): string
    {
        return TemplateRegexBuilder::getBuilder()
            ->name($this->getOpeningTag())
            ->useNumbers()
            ->includeForSearch('?\'.')
            ->setParentheses()
            ->useQuotes(false)
            ->getRegex();
    }

    public function getName(): string
    {
        return 'flash';
    }
}
