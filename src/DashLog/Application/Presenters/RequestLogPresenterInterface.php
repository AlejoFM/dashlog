<?php

namespace DashLog\Application\Presenters;

use DashLog\Domain\Entities\RequestLog;

interface RequestLogPresenterInterface
{
    public function present(RequestLog $data): array;
    public function presentCollection(array $logs): array;
    public function presentStats(array $stats): array;
} 