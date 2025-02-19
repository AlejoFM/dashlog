<?php

namespace DashLog\Domain\ValueObjects;

enum RequestStatus: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case REDIRECT = 'redirect';
    case CLIENT_ERROR = 'client_error';
    case SERVER_ERROR = 'server_error';

    public static function fromStatusCode(int $statusCode): self
    {
        return match (true) {
            $statusCode >= 200 && $statusCode < 300 => self::SUCCESS,
            $statusCode >= 300 && $statusCode < 400 => self::REDIRECT,
            $statusCode >= 400 && $statusCode < 500 => self::CLIENT_ERROR,
            $statusCode >= 500 => self::SERVER_ERROR,
            default => self::ERROR,
        };
    }
} 