<?php

namespace Fwt\Framework\Kernel\Login;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Config\FileConfig;
use Fwt\Framework\Kernel\Exceptions\Config\ValueIsNotConfiguredException;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\ObjectResolver;
use Fwt\Framework\Kernel\Session\Session;

class Authentication
{
    protected const SESSION_KEY = 'auth-token';

    protected Session $session;
    protected FileConfig $config;

    public function __construct()
    {
        $this->config = App::$app->getConfig('auth');
        $this->session = Session::start();
    }

    public function getUser(string $name = null): ?UserModel
    {
        $users = $this->config->get('user_classes');

        if (empty($users)) {
            throw new ValueIsNotConfiguredException('auth.user_classes');
        }

        if ($name) {
            return $this->getUserByClass($users[$name]);
        }

        foreach ($users as $userClass) {
            $user = $this->getUserByClass($userClass);

            if ($user) {
                return $user;
            }
        }

        return null;
    }

    public function getToken(): Token
    {
        return Token::fromString($this->session->get(self::SESSION_KEY));
    }

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

    protected function getUserByClass(string $userClass): ?UserModel
    {
        if (!is_subclass_of($userClass, UserModel::class)) {
            throw new InvalidExtensionException($userClass, UserModel::class);
        }

        return $userClass::getByToken($this->getToken());
    }
}
