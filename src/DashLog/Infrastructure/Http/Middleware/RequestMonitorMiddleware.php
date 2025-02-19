<?php
namespace DashLog\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use DashLog\Application\Services\RequestLogService;
use DashLog\Application\DTOs\RequestLogDTO;

class RequestMonitorMiddleware
{
    public function __construct(
        private RequestLogService $logService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        
        $this->logService->logRequest(
            RequestLogDTO::fromRequest($request, $response, $duration)
        );

        return $response;
    }
} 