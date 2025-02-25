<?php

namespace AledDev\DashLog\Infrastructure\Persistence\MongoDB;

use AledDev\DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use AledDev\DashLog\Domain\Entities\RequestLog;
use MongoDB\Client;

class MongoDBRequestLogRepository implements RequestLogRepositoryInterface
{
    private $collection;

    public function __construct()
    {
        $config = config('dashlog.storage.drivers.mongodb');
        $client = new Client($config['host']);
        $this->collection = $client->{$config['database']}->{$config['collection']};
    }

    public function save(RequestLog $log): void
    {
        $this->collection->insertOne([
            'method' => $log->getMethod(),
            'url' => $log->getUrl(),
            'ip' => $log->getIp(),
            'user_id' => $log->getUserId(),
            'duration' => $log->getDuration(),
            'status' => $log->getStatus(),
            'request_data' => $log->getRequestData(),
            'response_data' => $log->getResponseData(),
            'headers' => $log->getHeaders(),
            'cookies' => $log->getCookies(),
            'session' => $log->getSession(),
            'stack_trace' => $log->getStackTrace(),
            'created_at' => $log->getCreatedAt()
        ]);
    }

    public function findById(string $id): ?RequestLog
    {
        $log = $this->collection->findOne(['id' => $id]);
        return $log ? new RequestLog(
            $log['id'],
            $log['method'],
            $log['url'],
            $log['ip'],
            $log['user_id'],
            $log['duration'],
            $log['status'],
            $log['request_data'],
            $log['response_data'],
            $log['headers'],
            $log['cookies'],    
            $log['session'],
            $log['stack_trace'],
            $log['user_agent'],
            $log['created_at']
        ) : null;
    }

    public function getStats(): array
    {
        $stats = [
            'total_logs' => $this->collection->count(),
            'total_duration' => 0,
            'total_errors' => 0,
            'total_requests' => 0
        ];

        foreach ($this->collection->find() as $log) {
            $stats['total_duration'] += $log['duration'];
            $stats['total_errors'] += ($log['status'] >= 500);
            $stats['total_requests']++;
        }

        return $stats;
    }

    public function getPaginatedLogs(int $page, int $perPage): array
    {
        $skip = ($page - 1) * $perPage;
        $logs = $this->collection->find()->skip($skip)->limit($perPage);
        return iterator_to_array($logs);
    }
} 