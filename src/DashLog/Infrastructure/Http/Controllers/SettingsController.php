<?php

namespace DashLog\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class SettingsController extends Controller
{
    public function show()
    {
        return view('dashlog::settings');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'logging_enabled' => 'boolean',
            'exclude_paths' => 'nullable|string',
            'storage_driver' => 'required|string|in:mysql,elasticsearch,mongodb,redis',
            'logging_fields' => 'array',
            'max_body_size' => 'required|integer|min:1000|max:1000000',
            'stack_trace_limit' => 'required|integer|min:1|max:100',
            'ai_analysis_enabled' => 'boolean',
            'aiml_api_key' => 'required_if:ai_analysis_enabled,1|string',
            'provider' => 'required_if:ai_analysis_enabled,1|string',
            'model' => 'required_if:ai_analysis_enabled,1|string',
        ]);

        // Process exclude_paths correctly, removing unwanted characters
        $excludePaths = array_values(array_filter(
            explode("\n", $validated['exclude_paths'] ?? ''),
            fn($path) => !empty(trim($path))
        ));
        
        // Clean each path individually
        $excludePaths = array_map(function($path) {
            return trim(str_replace(["\r", "\n", "\r\n"], '', $path));
        }, $excludePaths);

        // Remove duplicates and reindex
        $excludePaths = array_values(array_unique($excludePaths));

        // Convert logging fields to booleans
        $loggingFields = array_map(
            fn($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            $validated['logging_fields'] ?? []
        );

        // Structure the configuration correctly
        $settings = [
            'enabled' => filter_var($validated['logging_enabled'], FILTER_VALIDATE_BOOLEAN),
            'exclude_paths' => $excludePaths,
            'middleware' => [
                'log' => \DashLog\Infrastructure\Http\Middleware\RequestMonitorMiddleware::class,
            ],
            'storage' => [
                'driver' => $validated['storage_driver'],
                'drivers' => config('dashlog.storage.drivers'),
            ],
            'logging' => [
                'fields' => $loggingFields,
                'sensitive_fields' => [
                    'password',
                    'password_confirmation',
                    'credit_card',
                ],
                'max_body_size' => (int)$validated['max_body_size'],
                'stack_trace_limit' => (int)$validated['stack_trace_limit'],
            ],
            'ai_analysis' => [
                'enabled' => filter_var($validated['ai_analysis_enabled'], FILTER_VALIDATE_BOOLEAN),
                'aiml_api_key' => $validated['aiml_api_key'] ?? null,
                'provider' => $validated['provider'] ?? null,
                'ai_model' => $validated['model'] ?? null,
            ],
        ];

        try {
            $this->updateConfig($settings);
            
            config(['dashlog.ai_analysis.provider' => $settings['ai_analysis']['provider']]);
            config(['dashlog.ai_analysis.ai_model' => $settings['ai_analysis']['ai_model']]);
            
            return redirect()
                ->back()
                ->with('success', 'Settings updated successfully');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }

    private function updateConfig(array $settings)
    {
        $configPath = config_path('dashlog.php');
        
        // Check if the file exists and is writable
        if (!is_writable($configPath)) {
            throw new \Exception("Config file is not writable: $configPath");
        }

        // Generate the file content
        $content = "<?php\n\nreturn [\n";
        $content .= "    /*\n";
        $content .= "    |--------------------------------------------------------------------------\n";
        $content .= "    | DashLog Configuration\n";
        $content .= "    |--------------------------------------------------------------------------\n";
        $content .= "    */\n\n";
        
        // Add each main section
        foreach ($settings as $key => $value) {
            $content .= "    '$key' => " . $this->formatValue($value, 1) . ",\n\n";
        }
        
        $content .= "];\n";

        // Try to write the file
        if (file_put_contents($configPath, $content) === false) {
            throw new \Exception("Failed to write config file");
        }

        // Clear the configuration cache
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($configPath, true);
        }
    }

    private function formatValue($value, $indent = 0): string
    {
        $space = str_repeat('    ', $indent);
        
        if (is_array($value)) {
            $lines = [];
            $isSequential = array_keys($value) === range(0, count($value) - 1);
            
            foreach ($value as $k => $v) {
                $formattedValue = $this->formatValue($v, $indent + 1);
                if ($isSequential) {
                    $lines[] = $space . '    ' . $formattedValue;
                } else {
                    $key = is_numeric($k) ? $k : "'$k'";
                    $lines[] = $space . '    ' . $key . ' => ' . $formattedValue;
                }
            }
            
            return "[\n" . implode(",\n", $lines) . "\n" . $space . ']';
        }
        
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        
        if (is_null($value)) {
            return 'null';
        }
        
        if (is_numeric($value)) {
            return (string)$value;
        }
        
        return "'" . addslashes($value) . "'";
    }
} 