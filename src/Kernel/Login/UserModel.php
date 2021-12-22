<?php

namespace Fwt\Framework\Kernel\Login;

use Fwt\Framework\Kernel\Database\Models\AbstractModel;
use Fwt\Framework\Kernel\Exceptions\Login\LoginException;
use Fwt\Framework\Kernel\Session\Session;

abstract class UserModel extends AbstractModel
{
    private static PasswordHasher $hasher;

    public static function create(array $data): AbstractModel
    {
        return parent::create(self::findAndHashPassword($data));
    }

    public static function createDry(array $data): AbstractModel
    {
        return parent::createDry(self::findAndHashPassword($data));
    }

    public static function login(array $data)
    {
        $passwordField = self::getPasswordField();
        $hasher = self::getPasswordHasher();

        if (!array_key_exists($passwordField, $data)) {
            throw LoginException::incorrectData();
        }

        $password = $data[$passwordField];
        unset($data[$passwordField]);

        $candidate = static::where($data);

        if (count($candidate) !== 1) {
            throw LoginException::incorrectData();
        }

        /** @var self $candidate */
        $candidate = $candidate[0];

        if (!$hasher->verify($password, $candidate->$passwordField)) {
            throw LoginException::incorrectData();
        }

        (new Authentication())->authenticateAs($candidate);

        return $candidate;
    }

    protected static function findAndHashPassword(array $data): array
    {
        $password = self::getPasswordField();
        $hasher = self::getPasswordHasher();

        if (array_key_exists($password, $data) && $hasher->needsRehash($data[$password])) {
            $data[$password] = $hasher->hash($data[$password]);
        }

        return $data;
    }

    protected static function getPasswordField(): string
    {
        return 'password';
    }

    protected static function getPasswordHasher(): PasswordHasher
    {
        if (!isset(self::$hasher)) {
            self::$hasher = new PasswordHasher();
        }

        return self::$hasher;
    }
}
