<?php

namespace DashLog\Domain\ValueObjects;

enum RequestStatus: string
{
    case SUCCESS = '200';
    case ERROR = 'error';
    case REDIRECT = '300';
    case CLIENT_ERROR = '400';
    case SERVER_ERROR = '500';

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