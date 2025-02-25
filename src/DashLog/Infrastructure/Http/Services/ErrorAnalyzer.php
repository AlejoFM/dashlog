<?php

namespace AledDev\DashLog\Infrastructure\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErrorAnalyzer
{
    private $apiKey;
    private $apiUrl = 'https://api.aimlapi.com/v1/chat/completions';
    private const MAX_TOKENS = 512; // Free plan limit

    public function __construct()
    {
        $this->apiKey = config('dashlog.ai_analysis.aiml_api_key');
    }

    public function analyze(array $log): ?array
    {
        if (!isset($log['details']['error'])) {
            return null;
        }

        try {
            $error = $log['details']['error'];
            $prompt = $this->buildPrompt($error);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => config('dashlog.ai_analysis.ai_model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'PHP/Laravel expert: analyze errors briefly.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => self::MAX_TOKENS
            ]);

            if (!$response->successful()) {
                throw new \Exception('AI API request failed: ' . $response->status());
            }

            $explanation = $response->json('choices.0.message.content', 'Analysis unavailable');
            $explanation = str_replace(["\n\n", "  "], ["\n", " "], trim($explanation));

            return [
                'explanation' => $explanation,
                'confidence_level' => 'high',
                'model_used' => config('dashlog.ai_analysis.ai_model'),
                'timestamp' => now()->toIso8601String()
            ];
        } catch (\Exception $e) {
            
            return [
                'explanation' => 'Error analysis failed: ' . $e->getMessage(),
                'confidence_level' => 'none',
                'model_used' => config('dashlog.ai_analysis.ai_model'),
                'timestamp' => now()->toIso8601String()
            ];
        }
    }

    private function buildPrompt(array $error): string
    {
        $message = $error['message'] ?? 'Unknown error';
        $file = basename($error['file'] ?? 'unknown');
        $line = $error['line'] ?? '?';
        
        $trace = !empty($error['trace']) ? $error['trace'][0] : null;
        $context = '';
        
        if ($trace) {
            $class = $trace['class'] ?? basename($trace['file'] ?? '');
            $function = $trace['function'] ?? 'unknown';
            $context = " in $class::$function()";
        }

        return "PHP error: '$message' at $file:$line$context";
    }
}