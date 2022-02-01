<?php

namespace Fwt\Framework\Kernel\Login;

use Fwt\Framework\Kernel\Database\ORM\Models\Model;
use Fwt\Framework\Kernel\Exceptions\Login\LoginException;
use Fwt\Framework\Kernel\Exceptions\RequiredArrayKeysException;

abstract class UserModel extends Model
{
    private static PasswordHasher $hasher;

    public static function create(array $data): Model
    {
        return parent::create(self::findAndHashPassword($data));
    }

    public static function createDry(array $data): Model
    {
        return parent::createDry(self::findAndHashPassword($data));
    }

    public static function getByToken(Token $token): ?self
    {
        $user = static::where('token', $token->getToken())->fetch();

        if (count($user) !== 1) {
            return null;
        }

        return $user[0];
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

        $username = static::getUsernameColumn();
        RequiredArrayKeysException::checkKeysExistence([$username], $data);

        $candidate = static::where($username, $data[$username])->fetch();

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

    public static function getUsernameColumn(): string
    {
        return 'email';
    }

    public function getUsername(): string
    {
        return $this->{static::getUsernameColumn()};
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
