<?php

namespace AledDev\DashLog\Domain\ValueObjects;

enum RequestMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';

    public static function fromString(string $method): self
    {
        return self::from(strtoupper($method));
    }
} 