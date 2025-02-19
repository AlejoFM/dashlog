<?php

namespace DashLog\Infrastructure\Factories;

use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use DashLog\Infrastructure\Persistence\Elasticsearch\ElasticsearchRequestLogRepository;
use DashLog\Infrastructure\Persistence\MySQL\MySQLRequestLogRepository;
use DashLog\Infrastructure\Persistence\MongoDB\MongoDBRequestLogRepository;
use DashLog\Infrastructure\Persistence\Redis\RedisRequestLogRepository;
use InvalidArgumentException;

class StorageDriverFactory
{
    public static function make(string $driver): RequestLogRepositoryInterface
    {
        return match ($driver) {
            'mysql' => new MySQLRequestLogRepository(),
            'mongodb' => self::createMongoRepository(),
            'elasticsearch' => self::createElasticsearchRepository(),
            'redis' => self::createRedisRepository(),
            default => throw new InvalidArgumentException("Unsupported storage driver: {$driver}")
        };
    }

    private static function createMongoRepository(): RequestLogRepositoryInterface
    {
        if (!class_exists(\MongoDB\Client::class)) {
            throw new \RuntimeException(
                'MongoDB driver is not installed. Please run: composer require mongodb/mongodb'
            );
        }
        return new MongoDBRequestLogRepository();
    }

    private static function createElasticsearchRepository(): RequestLogRepositoryInterface
    {
        if (!class_exists(\Elasticsearch\Client::class)) {
            throw new \RuntimeException(
                'Elasticsearch client is not installed. Please run: composer require elasticsearch/elasticsearch'
            );
        }
        return new ElasticsearchRequestLogRepository();
    }

    private static function createRedisRepository(): RequestLogRepositoryInterface
    {
        if (!class_exists(\Predis\Client::class)) {
            throw new \RuntimeException(
                'Redis client is not installed. Please run: composer require predis/predis'
            );
        }
        return new RedisRequestLogRepository();
    }
} 