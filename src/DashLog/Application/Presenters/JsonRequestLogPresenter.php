<?php

namespace AledDev\DashLog\Application\Presenters;

use AledDev\DashLog\Domain\Entities\RequestLog;

class JsonRequestLogPresenter implements RequestLogPresenterInterface
{
    public function present(RequestLog $data): array
    {
        return [
            'timestamp' => $data->getCreatedAt(),
            'method' => $data->getMethod(),
            'endpoint' => $data->getUrl(),
            'status' => $data->getStatus(),
            'duration_ms' => $data->getDuration() * 1000,
            'request' => $data->getRequestData() ?? null,
            'response' => $data->getResponseData() ?? null,
            'error' => $data->getStackTrace() ?? null,
        ];
    }

    public function presentCollection(array $logs): array
    {
        return [
            'data' => array_map(fn($log) => $this->present($log), $logs),
            'meta' => [
                'count' => count($logs),
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    public function presentStats(array $stats): array
    {
        return [
            'metrics' => [
                'total_requests' => $stats['total_requests'],
                'average_duration_ms' => $stats['avg_duration'] * 1000,
                'success_rate' => $stats['success_rate'],
                'error_rate' => $stats['error_rate'],
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }
} 