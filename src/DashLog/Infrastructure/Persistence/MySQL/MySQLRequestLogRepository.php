<?php

namespace DashLog\Infrastructure\Persistence\MySQL;

use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use DashLog\Domain\Entities\RequestLog;
use Illuminate\Support\Facades\DB;

class MySQLRequestLogRepository implements RequestLogRepositoryInterface
{
    private string $table;

    public function __construct()
    {
        $this->table = config('dashlog.storage.drivers.mysql.table');
    }

    public function save(RequestLog $log): void
    {
        DB::table($this->table)->insert([
            'method' => $log->getMethod(),
            'url' => $log->getUrl(),
            'ip' => $log->getIp(),
            'user_id' => $log->getUserId(),
            'duration' => $log->getDuration(),
            'status_code' => $log->getStatus(),
            'request' => json_encode($log->getRequestData()),
            'response' => json_encode($log->getResponseData()),
            'headers' => json_encode($log->getHeaders()),
            'cookies' => json_encode($log->getCookies()),
            'session' => json_encode($log->getSession()),
            'stack_trace' => json_encode($log->getStackTrace()),
            'user_agent' => $log->getUserAgent(),
            'created_at' => $log->getCreatedAt()
        ]);
    }

    public function findById(string $id): ?RequestLog
    {
        $log = DB::table($this->table)->find($id);
        if (!$log) return null;

        return new RequestLog(
            $log->id,
            $log->method,
            $log->url,
            $log->ip,
            $log->user_id,
            $log->duration,
            $log->status_code,
            json_decode($log->request, true),
            json_decode($log->response, true),
            json_decode($log->headers, true),
            json_decode($log->cookies, true),
            json_decode($log->session, true),
            json_decode($log->stack_trace, true),
            new \DateTimeImmutable($log->created_at),
            $log->user_agent
        );
    }

    public function getStats(): array
    {
        $stats = [
            'total_requests' => DB::table($this->table)->count(),
            'total_duration' => DB::table($this->table)->avg('duration'),
            'success_rate' => $this->calculateSuccessRate(),
            'error_rate' => $this->calculateErrorRate(),
        ];
        return $stats;
    }
    private function calculateSuccessRate()
    {
        $total = DB::table($this->table)->count();
        if ($total === 0) return 0;

        $success = DB::table($this->table)
            ->where('status_code', '<', 400)
            ->count();

        return round(($success / $total) * 100);
    }   
    private function calculateErrorRate()
    {
        $total = DB::table($this->table)->count();
        if ($total === 0) return 0;

        $errors = DB::table($this->table)
            ->where('status_code', '>=', 400)
            ->count();

        return round(($errors / $total) * 100);
    }   
    
    public function getPaginatedLogs(int $page, int $perPage): array
    {
        $skip = ($page - 1) * $perPage;
        $logs = DB::table($this->table)
            ->orderBy('created_at', 'desc')
            ->skip($skip)
            ->take($perPage)
            ->get();

        return $logs->map(function($log) {
            $log->status = $log->status_code < 400 ? 'success' : 'error';
            return $log;
        })->toArray();
    }
} 