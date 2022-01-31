<?php

namespace Fwt\Framework\Kernel\Csrf;

use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\Login\Authentication;
use Fwt\Framework\Kernel\ObjectResolver;

class EncryptedTokenCsrfValidator extends CsrfValidator
{
    protected const SECRET_KEY = '33Sht?U<up-~f=>@xy8sah3uwA?T(<E8gE92vh5]rs4M3%-EbX,u9SqCk6jQ)}-J';
    protected const SEPARATOR = '!|!';

    protected ?string $authName;
    protected string $algorithm;
    protected Authentication $auth;

    public function __construct(string $authName = null, string $algorithm = null)
    {
        $this->authName = $authName;

        if (!is_null($algorithm)) {
            IllegalValueException::checkValue($algorithm, openssl_get_cipher_methods());
        }

        $this->algorithm = $algorithm ?? 'aes-256-ctr';
        $this->auth = container(ObjectResolver::class)->resolve(Authentication::class);
    }

    public function generate(): string
    {
        $userId = $this->auth->getUser($this->authName)->getUserIdentifier();

        $nonceSize = openssl_cipher_iv_length($this->algorithm);
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $ciphertext = openssl_encrypt($userId . self::SEPARATOR . time(),
            $this->algorithm,
            self::SECRET_KEY,
            OPENSSL_RAW_DATA,
            $nonce,
        );

        return base64_encode($nonce.$ciphertext);
    }

    public function isValid(string $csrfToken): bool
    {
        $csrfToken = base64_decode($csrfToken, true);
        $nonceSize = openssl_cipher_iv_length($this->algorithm);
        $nonce = mb_substr($csrfToken, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($csrfToken, $nonceSize, null, '8bit');

        $decrypted = openssl_decrypt(
            $ciphertext,
            $this->algorithm,
            self::SECRET_KEY,
            OPENSSL_RAW_DATA,
            $nonce
        );

        [$userId, $time] = explode(self::SEPARATOR, $decrypted);
        dd($userId, $time);
    }

    protected function getUserId(): string
    {
        return $this->auth->getUser($this->authName)->getUserIdentifier();
    }
}
