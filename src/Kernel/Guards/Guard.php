<?php

namespace FW\Kernel\Guards;

use FW\Kernel\Login\Authentication;
use FW\Kernel\Login\UserModel;
use FW\Kernel\ObjectResolver;

abstract class Guard
{
    public function getName(): string
    {
        return static::class;
    }

    protected function getUser(string $name = null): ?UserModel
    {
        $auth = container(ObjectResolver::class)->resolve(Authentication::class);

        return $auth->getUser($name);
    }
}
