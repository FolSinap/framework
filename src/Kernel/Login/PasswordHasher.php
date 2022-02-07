<?php

namespace FW\Kernel\Login;

class PasswordHasher
{
    public function hash(string $password, $algo =  PASSWORD_BCRYPT): string
    {
        return password_hash($password, $algo);
    }

    public function isHashed(string $password, $algo =  PASSWORD_BCRYPT): bool
    {
        return !$this->needsRehash($password, $algo);
    }

    public function needsRehash(string $password, $algo =  PASSWORD_BCRYPT): bool
    {
        return password_needs_rehash($password, $algo);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
