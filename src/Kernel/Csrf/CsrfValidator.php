<?php

namespace Fwt\Framework\Kernel\Csrf;

use Fwt\Framework\Kernel\Exceptions\Csrf\UndefinedCsrfValidatorException;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\Exceptions\Resolver\ObjectResolverException;
use Fwt\Framework\Kernel\ObjectResolver;

abstract class CsrfValidator
{
    public const SYNCHRONIZER_TOKENS_PATTERN = 'synchronizer_token'; //best choice
    public const DOUBLE_SUBMIT_COOKIE_PATTERN = 'double_submit_cookie';
    public const ENCRYPTED_TOKEN_PATTERN = 'encrypted_token'; //not recommended
    public const TOKEN_KEY = '_token';

    abstract public function generate(): string;

    abstract public function isValid(string $csrfToken): bool;

    public static function getValidator(): self
    {
        $validator = config('app.csrf.validator');
        $resolver = container(ObjectResolver::class);

        try {
            switch ($validator) {
                case '':
                case null:
                case self::SYNCHRONIZER_TOKENS_PATTERN:
                    $validator = $resolver->resolve(SynchronizerCsrfValidator::class);

                    break;
                case self::ENCRYPTED_TOKEN_PATTERN:
                    $validator = $resolver->resolve(EncryptedTokenCsrfValidator::class);

                    break;
                case self::DOUBLE_SUBMIT_COOKIE_PATTERN:
                    $validator = $resolver->resolve(DoubleSubmitCookieCsrfValidator::class);

                    break;
                default:
                    $validator = $resolver->resolve($validator);
            }
        } catch (ObjectResolverException $exception) {
            throw new UndefinedCsrfValidatorException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!$validator instanceof self) {
            throw new InvalidExtensionException(get_class($validator), self::class);
        }

        return $validator;
    }
}
