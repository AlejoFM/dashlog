<?php

namespace DashLog\Infrastructure\Persistence\Redis;

use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use DashLog\Domain\Entities\RequestLog;
use Illuminate\Support\Facades\Redis;

class RedisRequestLogRepository implements RequestLogRepositoryInterface
{
    private $redis;
    private $config;

    public function __construct()
    {
        $this->config = config('dashlog.storage.drivers.redis');
        $this->redis = Redis::connection($this->config['connection']);
    }

    public function save(RequestLog $log): void
    {
        $key = $this->config['key_prefix'] . uniqid();
        $data = [
            'id' => $log->getId(),
            'method' => $log->getMethod(),
            'url' => $log->getUrl(),
            'ip' => $log->getIp(),
            'user_id' => $log->getUserId(),
            'duration' => $log->getDuration(),
            'status' => $log->getStatus(),
            'request_data' => json_encode($log->getRequestData()),
            'response_data' => json_encode($log->getResponseData()),
            'headers' => json_encode($log->getHeaders()),
            'cookies' => json_encode($log->getCookies()),
            'session' => json_encode($log->getSession()),
            'stack_trace' => json_encode($log->getStackTrace()),
            'created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        $this->redis->hmset($key, $data);
        $this->redis->expire($key, $this->config['ttl']);
    }

    public function findById(string $id): ?RequestLog
    {
        $key = $this->config['key_prefix'] . $id;
        $data = $this->redis->hgetall($key);

        if (empty($data)) {
            return null;
        }

        return new RequestLog(
            $data['id'],
            $data['method'],    
            $data['url'],
            $data['ip'],
            $data['user_id'],
            $data['duration'],
            $data['status'],
            json_decode($data['request_data'], true),
            json_decode($data['response_data'], true),
            json_decode($data['headers'], true),
            json_decode($data['cookies'], true),
            json_decode($data['session'], true),
            json_decode($data['stack_trace'], true),
            new \DateTimeImmutable($data['created_at']),
            $data['user_agent'],
        );
    }

    public function getStats(): array
    {
        $keys = $this->redis->keys($this->config['key_prefix'] . '*');
        $stats = [
            'total_logs' => count($keys),
            'total_duration' => 0,
            'total_errors' => 0,
            'total_requests' => 0
        ];

        foreach ($keys as $key) {
            $data = $this->redis->hgetall($key);
            $stats['total_duration'] += $data['duration'];
            $stats['total_errors'] += ($data['status'] >= 500);
            $stats['total_requests']++;
        }

        return $stats;
    }

    public function getPaginatedLogs(int $page, int $perPage): array
    {
        $keys = $this->redis->keys($this->config['key_prefix'] . '*');
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage - 1;

        $logs = [];
        for ($i = $start; $i <= $end && $i < count($keys); $i++) {
            $logs[] = $this->findById($keys[$i]);
        }

        return $logs;
    }
} 