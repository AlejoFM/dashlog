<?php

namespace AledDev\DashLog\Application\Presenters;

use AledDev\DashLog\Domain\Entities\RequestLog;

interface RequestLogPresenterInterface
{
    public function present(RequestLog $data): array;
    public function presentCollection(array $logs): array;
    public function presentStats(array $stats): array;
} 