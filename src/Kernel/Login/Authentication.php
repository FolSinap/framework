<?php

namespace FW\Kernel\Login;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Exceptions\Config\ValueIsNotConfiguredException;
use FW\Kernel\Exceptions\InvalidExtensionException;
use FW\Kernel\Storage\Session;

class Authentication
{
    protected const SESSION_KEY = 'username';

    protected Session $session;
    protected FileConfig $config;

    public function __construct()
    {
        $this->config = config('auth');
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

        if (!($username = $this->getUsername($name))) {
            return null;
        }

        return $this->getUserByClass($users[$name], $username);
    }

    public function getUsername(string $name): ?string
    {
        if (!$this->session->has(self::SESSION_KEY) || !isset($this->session->get(self::SESSION_KEY)[$name])) {
            return null;
        }

        return $this->session->get(self::SESSION_KEY)[$name];
    }

    public function authenticateAs(UserModel $user): void
    {
        $classes = $this->config->get('user_classes');
        $class = get_class($user);

        if (!in_array($class, $classes)) {
            throw new ValueIsNotConfiguredException('auth.user_classes');
        }

        $name = array_flip($classes)[$class];

        $this->session->set(self::SESSION_KEY, [$name => $user->getUsername()]);
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

    protected function getUserByClass(string $userClass, string $username): ?UserModel
    {
        if (!is_subclass_of($userClass, UserModel::class)) {
            throw new InvalidExtensionException($userClass, UserModel::class);
        }

        return $userClass::getByUsername($username);
    }
}
