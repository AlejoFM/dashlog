<?php

namespace DashLog\Infrastructure\Persistence\Elasticsearch;

use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use DashLog\Domain\Entities\RequestLog;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchRequestLogRepository implements RequestLogRepositoryInterface
{
    private Client $client;
    private string $index = 'request_logs';

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([config('dashlog.elasticsearch.host')])
            ->build();
    }

    public function save(RequestLog $log): void
    {
        $this->client->index([
            'index' => $this->index,
            'body' => [
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
                'user_agent' => $log->getUserAgent(),
                'created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function getStats(): array
    {
        $response = $this->client->search([
            'index' => $this->index,
            'body' => [
                'aggs' => [
                    'avg_duration' => ['avg' => ['field' => 'duration']],
                    'status_counts' => ['terms' => ['field' => 'status.keyword']]
                ]
            ]
        ]);

        $aggs = $response['aggregations'];
        $total = $this->client->count(['index' => $this->index])['count'];

        return [
            'total_requests' => $total,
            'avg_duration' => $aggs['avg_duration']['value'] ?? 0,
            'success_rate' => $this->calculateSuccessRate($aggs['status_counts']['buckets']),
            'error_rate' => $this->calculateErrorRate($aggs['status_counts']['buckets'])
        ];
    }
    private function calculateSuccessRate(array $buckets)
    {
        $total = $this->client->count(['index' => $this->index])['count'];
        if ($total === 0) return 0;

        $success = $this->client->count([
            'index' => $this->index,
            'body' => [
                'query' => [
                    'range' => [
                        'status' => [
                            'lte' => 399
                        ]
                    ]
                ]
            ]
        ]);

        return round(($success / $total) * 100);
    }

    private function calculateErrorRate(array $buckets)
    {
        $total = $this->client->count(['index' => $this->index])['count'];
        if ($total === 0) return 0;

        $errors = $this->client->count([
            'index' => $this->index,
            'body' => [
                'query' => [
                    'range' => [
                        'status' => [
                            'gte' => 400
                        ]
                    ]
                ]
            ]
        ]);

        return round(($errors / $total) * 100);
    }
    public function getPaginatedLogs(int $page, int $perPage): array
    {
        $response = $this->client->search([
            'index' => $this->index,
            'body' => [
                'from' => ($page - 1) * $perPage,
                'size' => $perPage,
                'sort' => [
                    'created_at' => ['order' => 'desc']
                ]
            ]
        ]);

        return [
            'data' => array_map(fn($hit) => $hit['_source'], $response['hits']['hits']),
            'total' => $response['hits']['total']['value'],
            'per_page' => $perPage,
            'current_page' => $page
        ];
    }

    public function findById(string $id): ?RequestLog
    {
        try {
            $response = $this->client->get([
                'index' => $this->index,
                'id' => $id
            ]);
            
            return $this->mapToEntity($response['_source']);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function mapToEntity(array $data): RequestLog
    {
        return new RequestLog(
            $data['id'],
            $data['method'],
            $data['url'],
            $data['ip'],
            $data['user_id'],
            $data['duration'],
            $data['status'],
            $data['request_data'],
            $data['response_data'],
            $data['headers'],
            $data['cookies'],
            $data['session'],
            $data['stack_trace'],
            $data['user_agent'],
            $data['created_at']
        );
    }
} 