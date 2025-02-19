<?php

namespace DashLog\Infrastructure\Persistence\Eloquent\Repositories;

use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use DashLog\Domain\Entities\RequestLog;
use DashLog\Domain\ValueObjects\RequestMethod;
use DashLog\Domain\ValueObjects\RequestStatus;
use DashLog\Infrastructure\Persistence\Eloquent\Models\RequestLogModel;
use DateTimeImmutable;

class EloquentRequestLogRepository implements RequestLogRepositoryInterface
{
    public function save(RequestLog $log): void
    {
        RequestLogModel::create([
            'method' => $log->getMethod()->value,
            'url' => $log->getUrl(),
            'ip' => $log->getIp(),
            'user_id' => $log->getUserId(),
            'duration' => $log->getDuration(),
            'status' => $log->getStatus()->value,
            'request_data' => $log->getRequestData(),
            'response_data' => $log->getResponseData(),
            'headers' => $log->getHeaders(),
            'cookies' => $log->getCookies(),
            'session' => $log->getSession(),
            'stack_trace' => $log->getStackTrace(),
        ]);
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
            requestData: json_decode($model->request, true),
            responseData: json_decode($model->response, true),
            headers: json_decode($model->headers, true),
            cookies: json_decode($model->cookies, true),
            session: json_decode($model->session, true),
            stackTrace: json_decode($model->stack_trace, true),
            createdAt: new DateTimeImmutable($model->created_at)
        );
    }

    public function getStats(): array
    {
        $stats = [
            'total_requests' => RequestLogModel::count(),
            'total_duration' => RequestLogModel::sum('duration'),
            'total_errors' => RequestLogModel::where('status', 'error')->count(),
            'total_redirects' => RequestLogModel::where('status', 'redirect')->count(),
            'total_client_errors' => RequestLogModel::where('status', 'client_error')->count(),
            'total_server_errors' => RequestLogModel::where('status', 'server_error')->count(),
            'total_success' => RequestLogModel::where('status', 'success')->count(),
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
                requestData: $model->request,
                responseData: $model->response,
                headers: json_decode($model->headers, true),
                cookies: json_decode($model->cookies, true),
                session: json_decode($model->session, true),
                stackTrace: json_decode($model->stack_trace, true),
                createdAt: DateTimeImmutable::createFromInterface($model->created_at),
            );
        })->toArray();
    }
} 