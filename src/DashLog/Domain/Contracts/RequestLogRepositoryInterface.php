<?php

namespace DashLog\Domain\Contracts;

use DashLog\Domain\Entities\RequestLog;

interface RequestLogRepositoryInterface
{
    public function save(RequestLog $log): void;
    public function findById(string $id): ?RequestLog;
    public function getStats(): array;
    public function getPaginatedLogs(int $page, int $perPage): array;
} 