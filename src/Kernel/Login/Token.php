<?php

namespace Fwt\Framework\Kernel\Login;

class Token
{
    protected string $token;

    public function __construct(string $token = null)
    {
        $this->token = $token ?? $this->generate();
    }

    public static function fromString(string $string): self
    {
        return new static($string);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    protected function generate(int $length = 30): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
