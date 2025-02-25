<?php
namespace DashLog\Application\DTOs;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class RequestLogDTO
{
    public function __construct(
        public string $method,
        public string $url,
        public string $ip,
        public ?string $userId,
        public float $duration,
        public int $statusCode,
        public array $requestData,
        public ?array $responseData,
        public array $headers,
        public array $cookies,
        public array $session,
        public ?array $stackTrace,
        public string $userAgent,
    ) {}

    public static function fromRequest(Request $request, $response, float $duration, ?Throwable $exception = null): self
    {
        $config = config('dashlog.logging.fields');
        $maxSize = config('dashlog.logging.max_body_size');
        
        return new self(
            method: $request->method(),
            url: $request->fullUrl(),
            ip: $request->ip(),
            userId: auth()->id(),
            duration: $duration,
            statusCode: $response->status(),
            requestData: $config['request_body'] ? self::sanitizeData($request->all(), $maxSize) : null,
            responseData: $config['response_body'] ? self::sanitizeData(self::getResponseData($response), $maxSize) : null,
            headers: $config['headers'] ? self::sanitizeHeaders($request->headers->all()) : null,
            cookies: $config['cookies'] ? $request->cookies->all() : null,
            session: $config['session'] ? session()->all() : null,
            stackTrace: $config['stack_trace'] && $exception ? self::formatStackTrace($exception) : null,
            userAgent: $request->userAgent()
        );
    }

    private static function sanitizeData(array $data, int $maxSize): array
    {
        $sensitiveFields = config('dashlog.logging.sensitive_fields', []);
        
        // Eliminar campos sensibles
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '******';
            }
        }

        $json = json_encode($data);
        if (strlen($json) > $maxSize) {
            return ['warning' => 'Data exceeded maximum size limit'];
        }

        return $data;
    }

    private static function formatStackTrace(Throwable $exception): array
    {
        $limit = config('dashlog.logging.stack_trace_limit');
        $trace = array_slice($exception->getTrace(), 0, $limit);
        
        return [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $trace
        ];
    }

    private static function sanitizeHeaders(array $headers): array
    {
        $sensitive = ['cookie', 'authorization'];
        return array_diff_key($headers, array_flip($sensitive));
    }

    private static function getResponseData($response): array
    {
        if (method_exists($response, 'getData')) {
            return $response->getData(true);
        }
        
        $content = $response->getContent();
        return is_string($content) ? json_decode($content, true) ?? [] : [];
    }
}