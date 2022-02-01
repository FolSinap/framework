<?php

namespace Fwt\Framework\Kernel\Login;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Config\FileConfig;
use Fwt\Framework\Kernel\Exceptions\Config\ValueIsNotConfiguredException;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\Storage\Session;

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
        $name = $name ?? 'main';
        $users = $this->config->get('user_classes');

        if (empty($users)) {
            throw new ValueIsNotConfiguredException('auth.user_classes');
        } elseif (!array_key_exists($name, $users)) {
            throw new ValueIsNotConfiguredException("auth.user_classes.$name");
        }

        if (!($token = $this->getToken($name))) {
            return null;
        }

        return $this->getUserByClass($users[$name], $token);
    }

    public function getToken(string $name): ?Token
    {
        if (!$this->session->has(self::SESSION_KEY) || !isset($this->session->get(self::SESSION_KEY)[$name])) {
            return null;
        }

        return Token::fromString($this->session->get(self::SESSION_KEY)[$name]);
    }

    public function authenticateAs(UserModel $user): void
    {
        $classes = $this->config->get('user_classes');
        $class = get_class($user);

        if (!in_array($class, $classes)) {
            throw new ValueIsNotConfiguredException('auth.user_classes');
        }

        $name = array_flip($classes)[$class];
        $token = (new Token())->getToken();

        $user->update(['token' => $token]);

        $this->session->set(self::SESSION_KEY, [$name => $token]);
    }

    public function unAuthenticate(): void
    {
        if ($this->isAuthenticated()) {
            $this->session->unset(self::SESSION_KEY);
        }
    }

    public function unAuthenticateAs(string $name = 'main'): void
    {
        if ($this->isAuthenticated()) {
            $authentications = $this->session->get(self::SESSION_KEY);

            if (array_key_exists($name, $authentications)) {
                unset($authentications[$name]);
            }
        }
    }

    public function isAuthenticatedAs(string $name): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        $authentications = $this->session->get(self::SESSION_KEY);

        return array_key_exists($name, $authentications);
    }

    public function isAuthenticated(): bool
    {
        if ($this->session->has(self::SESSION_KEY)) {
            return true;
        }

        return false;
    }

    protected function getUserByClass(string $userClass, Token $token): ?UserModel
    {
        if (!is_subclass_of($userClass, UserModel::class)) {
            throw new InvalidExtensionException($userClass, UserModel::class);
        }

        return $userClass::getByToken($token);
    }
}
