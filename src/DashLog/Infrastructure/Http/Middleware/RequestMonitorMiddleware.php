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
    ) {
    }

    public function handle(Request $request, Closure $next)
    {

        if (!config('dashlog.enabled', true)) {
            return $next($request);
        }

        if ($this->shouldExclude($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        
        try {
            $response = $next($request);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $duration = microtime(true) - $startTime;

            $requestLog = new RequestLogDTO(
                method: $request->method(),
                url: $request->fullUrl(),
                ip: $request->ip(),
                userId: $request->user()?->id,
                duration: $duration,
                statusCode: isset($response) ? $response->status() : 500,
                requestData: $request->all(),
                responseData: isset($response) ? $this->getResponseContent($response) : null,
                headers: $this->sanitizeHeaders($request->headers->all()),
                cookies: $request->cookies->all(),
                session: $request->session()->all(),
                stackTrace: $response->status() >= 400 ? $this->getStackTrace() : null,
                userAgent: $request->userAgent() ?? 'Unknown',
            );

            $this->logService->logRequest($requestLog);
        }

        return $response ?? null;
    }

    private function shouldExclude(Request $request): bool
    {
        $path = $request->path();
        $excludePaths = config('dashlog.exclude_paths', [
            'dashlog/*',
            '_debugbar/*',
            '_ignition/*',
            'horizon/*'
        ]);

        foreach ($excludePaths as $pattern) {
            if (str_ends_with($pattern, '*')) {
                $basePattern = rtrim($pattern, '*');
                if (str_starts_with($path, $basePattern)) {
                    return true;
                }
            }
            elseif ($path === $pattern) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeHeaders(array $headers): array
    {
        $maskHeaders = config('dashlog.logging.mask_headers', []);

        foreach ($maskHeaders as $header) {
            if (isset($headers[strtolower($header)])) {
                $headers[strtolower($header)] = ['*****'];
            }
        }

        return $headers;
    }

    private function getResponseContent($response): ?array
    {
        try {
            $content = $response->getContent();
            return json_decode($content, true) ?? ['content' => $content];
        } catch (\Exception $e) {
            return ['error' => 'Could not decode response content'];
        }
    }

    private function getStackTrace(): array
    {
        return array_map(function ($trace) {
            return [
                'file' => $trace['file'] ?? null,
                'line' => $trace['line'] ?? null,
                'function' => $trace['function'] ?? null,
                'class' => $trace['class'] ?? null,
            ];
        }, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
    }
} 