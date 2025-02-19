<?php

namespace DashLog\Application\Presenters;

use Carbon\Carbon;

class DefaultRequestLogPresenter implements RequestLogPresenterInterface
{
    public function present(array $data): array
    {
        return [
            'id' => $data['id'] ?? null,
            'method' => $data['method'],
            'url' => $data['url'],
            'status' => [
                'code' => $data['status_code'],
                'text' => $this->getStatusText($data['status_code']),
                'class' => $this->getStatusClass($data['status_code']),
            ],
            'duration' => [
                'raw' => $data['duration'],
                'formatted' => $this->formatDuration($data['duration']),
            ],
            'timestamp' => [
                'raw' => $data['created_at'],
                'formatted' => Carbon::parse($data['created_at'])->diffForHumans(),
            ],
            'details' => $this->formatDetails($data),
        ];
    }

    public function presentCollection(array $logs): array
    {
        return array_map(fn($log) => $this->present($log), $logs);
    }

    public function presentStats(array $stats): array
    {
        return [
            'total_requests' => number_format($stats['total_requests']),
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

    private function formatDetails(array $data): array
    {
        $details = [];

        if (!empty($data['request_data'])) {
            $details['request'] = $data['request_data'];
        }

        if (!empty($data['response_data'])) {
            $details['response'] = $data['response_data'];
        }

        if (!empty($data['stack_trace'])) {
            $details['error'] = $data['stack_trace'];
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