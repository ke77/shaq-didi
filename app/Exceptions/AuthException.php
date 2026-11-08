<?php

namespace App\Exceptions;

class AuthException extends ApiException
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials.', 401);
    }
}