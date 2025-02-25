<?php

namespace AledDev\DashLog\Application\Presenters;

use Carbon\Carbon;
use AledDev\DashLog\Domain\Entities\RequestLog;

class DefaultRequestLogPresenter implements RequestLogPresenterInterface
{
    public function present(RequestLog $data): array
    {
        $presented = [
            'id' => $data->getId() ?? null,
            'method' => $data->getMethod(),
            'url' => $data->getUrl(),
            'status' => [
                'code' => $data->getStatus()->value,
                'text' => $this->getStatusText(intval($data->getStatus()->value)),
                'class' => $this->getStatusClass(intval($data->getStatus()->value)),
            ],
            'duration' => [
                'raw' => $data->getDuration(),
                'formatted' => $this->formatDuration(duration: $data->getDuration()),
            ],
            'timestamp' => [
                'raw' => $data->getCreatedAt(),
                'formatted' => Carbon::parse($data->getCreatedAt())->diffForHumans(),
            ],
            'details' => $this->formatDetails($data),
            'user_agent' => $data->getUserAgent(),
            'user_id' => $data->getUserId(),
            'request' => $data->getRequestData(),
            'response' => $data->getResponseData(),
            'cookies' => $data->getCookies(),
            'session' => $data->getSession(),
            'headers' => $data->getHeaders(),
        ];

        return $presented;
    }

    public function presentCollection(array $logs): array
    {
        return array_map(fn($log) => $this->present($log), $logs);
    }

    public function presentStats(array $stats): array
    {
        return [
            'total_requests' => number_format(num: $stats['total_requests']),
            'average_duration' => $this->formatDuration($stats['avg_duration']),
            'success_rate' => [
                'value' => $stats['success_rate'],
                'formatted' => number_format($stats['success_rate'], 1) . '%',
                'class' => $this->getSuccessRateClass($stats['success_rate']),
            ],
            'error_rate' => [
                'value' => $stats['error_rate'],
                'formatted' => number_format($stats['error_rate'], 1) . '%',
                'class' => $this->getErrorRateClass($stats['error_rate']),
            ],
        ];
    }

    private function formatDuration(float $duration): string
    {
        if ($duration < 1) {
            return round($duration * 1000) . 'ms';
        }
        return round($duration, 2) . 's';
    }

    private function getStatusText(int $code): string
    {
        return match (true) {
            $code >= 200 && $code < 300 => 'Success',
            $code >= 300 && $code < 400 => 'Redirect',
            $code >= 400 && $code < 500 => 'Client Error',
            $code >= 500 => 'Server Error',
            default => 'Unknown',
        };
    }

    private function getStatusClass(int $code): string
    {
        return match (true) {
            $code >= 200 && $code < 300 => 'success',
            $code >= 300 && $code < 400 => 'info',
            $code >= 400 && $code < 500 => 'warning',
            $code >= 500 => 'danger',
            default => 'default',
        };
    }

    private function formatDetails(RequestLog $data): array
    {
        $details = [];

        if (!empty($data->getRequestData())) {
            $details['request'] = $data->getRequestData();
        }

        if (!empty($data->getResponseData())) {
            $details['response'] = $data->getResponseData();
        }

        if (!empty($data->getStackTrace())) {
            $details['error'] = $data->getStackTrace();
        }

        return $details;
    }

    private function getSuccessRateClass(float $rate): string
    {
        return match (true) {
            $rate >= 98 => 'success',
            $rate >= 95 => 'info',
            $rate >= 90 => 'warning',
            default => 'danger',
        };
    }

    private function getErrorRateClass(float $rate): string
    {
        return match (true) {
            $rate <= 2 => 'success',
            $rate <= 5 => 'info',
            $rate <= 10 => 'warning',
            default => 'danger',
        };
    }
} 