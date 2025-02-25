<?php

namespace DashLog\Infrastructure\Persistence\Eloquent\Repositories;

use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use DashLog\Domain\Entities\RequestLog;
use DashLog\Domain\ValueObjects\RequestMethod;
use DashLog\Domain\ValueObjects\RequestStatus;
use DashLog\Infrastructure\Persistence\Eloquent\Models\RequestLogModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;

class EloquentRequestLogRepository implements RequestLogRepositoryInterface
{
    public function save(RequestLog $log): void
    {
        try {

            RequestLogModel::create([
                'id' => $log->getId(),
                'method' => $log->getMethod()->value,
                'url' => $log->getUrl(),
                'ip' => $log->getIp(),
                'user_id' => $log->getUserId(),
                'duration' => $log->getDuration(),
                'status_code' => $log->getStatus()->value,
                'request_data' => json_encode($log->getRequestData()),
                'response_data' => json_encode($log->getResponseData()),
                'headers' => json_encode($log->getHeaders()),
                'cookies' => json_encode($log->getCookies()),
                'session' => json_encode($log->getSession()),
                'stack_trace' => json_encode($log->getStackTrace()),
                'user_agent' => $log->getUserAgent(),
                'created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $log->getCreatedAt()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function findById(string $id): ?RequestLog
    {
        $model = RequestLogModel::find($id);
        if (!$model) {
            return null;
        }
        return new RequestLog(
            id: (string)$model->id,
            method: RequestMethod::fromString($model->method),
            url: $model->url,
            ip: $model->ip,
            userId: (string)$model->user_id,    
            duration: (float)$model->duration,
            status: RequestStatus::fromStatusCode($model->status_code),
            requestData: json_decode($model->request_data, true),
            responseData: json_decode($model->response_data, true),
            headers: json_decode($model->headers, true),
            cookies: json_decode($model->cookies, true),
            session: json_decode($model->session, true),
            stackTrace: json_decode($model->stack_trace, true),
            userAgent: $model->user_agent,
            createdAt: new DateTimeImmutable($model->created_at)
        );
    }

    public function getStats(): array
    {
        $stats = [
            'total_requests' => RequestLogModel::count(),
            'total_duration' => RequestLogModel::sum('duration'),
            'total_errors' => RequestLogModel::where('status_code', '>=', 400)->count(),
            'total_redirects' => RequestLogModel::where('status_code', 300)->count(),
            'total_client_errors' => RequestLogModel::where('status_code', 400)->count(),
            'total_server_errors' => RequestLogModel::where('status_code', 500)->count(),
            'total_success' => RequestLogModel::where('status_code', '<', 400)->count(),
            'avg_duration' => RequestLogModel::avg('duration'),
            'success_rate' => RequestLogModel::where('status_code', '<', 400)->count() / RequestLogModel::count() * 100,
            'error_rate' => RequestLogModel::where('status_code', '>=', 400)->count() / RequestLogModel::count() * 100,
        ];
        
        return $stats;
    }

    public function getPaginatedLogs(int $page, int $perPage): array
    {
        $logs = RequestLogModel::orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $logs->map(function ($model) {
            return new RequestLog(
                id: $model->id,
                method: RequestMethod::fromString($model->method),
                url: $model->url,
                ip: $model->ip,
                userId: $model->user_id,
                duration: $model->duration,
                status: RequestStatus::fromStatusCode($model->status_code ?? 500),
                requestData: json_decode($model->request_data, true),
                responseData: json_decode($model->response_data, true),
                headers: json_decode($model->headers, true),
                cookies: json_decode($model->cookies, true),
                session: json_decode($model->session, true),
                stackTrace: json_decode($model->stack_trace, true),
                createdAt: DateTimeImmutable::createFromInterface($model->created_at),
                userAgent: $model->user_agent
            );
        })->toArray();
    }
} 