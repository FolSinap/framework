<?php

namespace Fwt\Framework\Kernel\Login;

use Fwt\Framework\Kernel\Session\Session;

class Authentication
{
    protected const SESSION_KEY = 'auth-token';

    protected Session $session;

    public function __construct()
    {
        $this->session = Session::start();
    }

    /**
     * TODO: ADD getUser() METHOD
     */

    public function authenticateAs(UserModel $user): void
    {
        $token = (new Token())->getToken();

        $user->update(['token' => $token]);

        $this->session->set(self::SESSION_KEY, $token);
    }

    public function unAuthenticate(): void
    {
        if ($this->isAuthenticated()) {
            $this->session->unset(self::SESSION_KEY);
        }
    }

    public function isAuthenticated(): bool
    {
        if ($this->session->has(self::SESSION_KEY)) {
            return true;
        }

        return false;
    }
}
