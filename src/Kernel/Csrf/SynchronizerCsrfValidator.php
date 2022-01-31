<?php

namespace Fwt\Framework\Kernel\Csrf;

use Fwt\Framework\Kernel\Storage\Session;

class SynchronizerCsrfValidator extends CsrfValidator
{
    protected const CSRF_TOKEN = 'csrf_token';

    protected Session $session;

    public function __construct()
    {
        $this->session = Session::start();
    }

    public function generate(): string
    {
        $token = bin2hex(random_bytes(32));

        $this->session->set(self::CSRF_TOKEN, $token);

        return $token;
    }

    public function isValid(string $csrfToken): bool
    {
        $token = $this->session->get(self::CSRF_TOKEN);

        if (!$token) {
            return false;
        }

        return hash_equals($token, $csrfToken);
    }
}
