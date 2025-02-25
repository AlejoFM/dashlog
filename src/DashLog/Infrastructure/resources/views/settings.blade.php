@extends('dashlog::layouts.app')

@section('title', 'DashLog Settings')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                DashLog Settings
            </h3>
        </div>
        
        <form action="{{ route('dashlog.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="space-y-6">
                    <!-- Storage Driver -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Storage Driver</h4>
                        <div class="mt-2 space-y-2">
                            @foreach(['mysql', 'elasticsearch', 'mongodb', 'redis'] as $driver)
                                <div class="flex items-center">
                                    <input type="radio" id="{{ $driver }}" name="storage_driver" value="{{ $driver }}"
                                        {{ config('dashlog.storage.driver', 'mysql') === $driver ? 'checked' : '' }}
                                        class="h-4 w-4 text-indigo-600">
                                    <label for="{{ $driver }}" class="ml-2 text-sm text-gray-700 capitalize">{{ $driver }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- AI Analysis Settings -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-medium text-gray-900">AI Analysis Settings</h4>
                        <div class="mt-4 space-y-4">
                            <!-- Enable/Disable AI Analysis -->
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" id="ai_analysis_enabled" name="ai_analysis_enabled" value="1"
                                        {{ config('dashlog.ai_analysis.enabled', false) ? 'checked' : '' }}
                                        class="h-4 w-4 text-indigo-600">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="ai_analysis_enabled" class="font-medium text-gray-700">Enable AI Analysis</label>
                                    <p class="text-gray-500">Allow DashLog to analyze errors using AI capabilities</p>
                                </div>
                            </div>

                            <!-- AI Provider -->
                            <div>
                                <label for="provider" class="block text-sm font-medium text-gray-700">AI Provider</label>
                                <select id="provider" name="provider" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Select Provider</option>
                                </select>
                            </div>

                            <!-- API Key -->
                            <div>
                                <label for="aiml_api_key" class="block text-sm font-medium text-gray-700">AIML API Key</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="password" name="aiml_api_key" id="aiml_api_key"
                                        value="{{ config('dashlog.ai_analysis.aiml_api_key') }}"
                                        class="block w-full pr-10 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <button type="button" 
                                        onclick="toggleApiKeyVisibility()"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center">
                                        <svg id="eye-icon" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg id="eye-off-icon" class="h-5 w-5 text-gray-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Model Selection -->
                            <div>
                                <label for="model" class="block text-sm font-medium text-gray-700">AI Model</label>
                                <select id="model" name="model" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">Select Model</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Logging Settings -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-medium text-gray-900">Logging Settings</h4>
                        
                        <!-- Enable/Disable Logging -->
                        <div class="mt-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" id="logging_enabled" name="logging_enabled" value="1"
                                        {{ config('dashlog.logging.enabled', true) ? 'checked' : '' }}
                                        class="h-4 w-4 text-indigo-600">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="logging_enabled" class="font-medium text-gray-700">Enable Request Monitoring</label>
                                    <p class="text-gray-500">Monitor and store incoming requests and responses</p>
                                </div>
                            </div>
                        </div>

                        <!-- Exclude Paths -->
                        <div class="mt-4">
                            <label for="exclude_paths" class="block text-sm font-medium text-gray-700">
                                Exclude Paths
                                <span class="text-gray-500 text-xs">(One path per line)</span>
                            </label>
                            <div class="mt-1">
                                <textarea id="exclude_paths" name="exclude_paths" rows="4"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="/api/health&#10;/api/metrics&#10;/api/status">{{ implode("\n", config('dashlog.exclude_paths', [])) }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Paths that should be excluded from monitoring. Use exact matches, one per line.
                            </p>
                        </div>

                        <!-- Fields to Log -->
                        <div class="mt-6">
                            <h4 class="text-sm font-medium text-gray-900">Fields to Log</h4>
                            <div class="mt-2 space-y-2">
                                @foreach([
                                    'request_body' => 'Request Body',
                                    'response_body' => 'Response Body',
                                    'headers' => 'Headers',
                                    'cookies' => 'Cookies',
                                    'session' => 'Session',
                                    'stack_trace' => 'Stack Trace'
                                ] as $field => $label)
                                    <div class="flex items-center">
                                        <input type="checkbox" id="{{ $field }}" name="logging_fields[{{ $field }}]" value="1"
                                            {{ config("dashlog.logging.fields.{$field}", false) ? 'checked' : '' }}
                                            class="h-4 w-4 text-indigo-600">
                                        <label for="{{ $field }}" class="ml-2 text-sm text-gray-700">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Additional Logging Settings -->
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="max_body_size" class="block text-sm font-medium text-gray-700">Max Body Size (bytes)</label>
                                    <input type="number" name="max_body_size" id="max_body_size"
                                        value="{{ config('dashlog.logging.max_body_size', 64000) }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="stack_trace_limit" class="block text-sm font-medium text-gray-700">Stack Trace Limit (lines)</label>
                                    <input type="number" name="stack_trace_limit" id="stack_trace_limit"
                                        value="{{ config('dashlog.logging.stack_trace_limit', 20) }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const MODELS_CACHE_KEY = 'aiml_api_models_cache';
const CACHE_DURATION = 24 * 60 * 60 * 1000; // 24 hours

let modelsByProvider = {}; // Global variable to store models

const currentProvider = @json(config('dashlog.ai_analysis.provider'));
const currentModel = @json(config('dashlog.ai_analysis.ai_model'));

async function fetchModels() {
    try {
        const apiKey = document.getElementById('aiml_api_key').value;
        if (!apiKey) {
            console.error('API key is required');
            return [];
        }

        const cachedData = localStorage.getItem(MODELS_CACHE_KEY);
        if (cachedData) {
            const { timestamp, models } = JSON.parse(cachedData);
            if (Date.now() - timestamp < CACHE_DURATION) {
                return models;
            }
        }

        const response = await fetch('https://api.aimlapi.com/models', {
            headers: {
                'Authorization': `Bearer ${apiKey}`,
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        const models = data.data
            .filter(model => model.type === 'chat-completion' && 
                           model.features?.includes('openai/chat-completion'))
            .map(model => ({
                id: model.id,
                name: model.info.name,
                description: model.info.description || '',
                contextLength: model.info.contextLength || 0,
                provider: model.info.developer || extractProviderFromId(model.id)
            }));

        localStorage.setItem(MODELS_CACHE_KEY, JSON.stringify({
            timestamp: Date.now(),
            models
        }));

        return models;
    } catch (error) {
        console.error('Error fetching models:', error);
        return [];
    }
}

function extractProviderFromId(id) {
    const parts = id.split('/');
    return parts.length > 1 ? parts[0] : 'Unknown';
}

async function initializeSelects() {
    const models = await fetchModels();
    const providerSelect = document.getElementById('provider');
    
    // Group models by provider
    modelsByProvider = models.reduce((acc, model) => {
        // Extract provider from model ID or use explicit provider
        const provider = model.provider || model.id.split('/')[0];
        if (!acc[provider]) {
            acc[provider] = [];
        }
        acc[provider].push(model);
        return acc;
    }, {});

    // Configure provider select
    providerSelect.innerHTML = '<option value="">Select Provider</option>';
    Object.keys(modelsByProvider).sort().forEach(provider => {
        const option = new Option(provider, provider);
        option.selected = provider === currentProvider;
        providerSelect.add(option);
    });

    // Update models if there is a selected provider
    if (currentProvider) {
        updateModelSelect(currentProvider);
    }

    // Add event listener for provider changes
    providerSelect.addEventListener('change', (e) => {
        updateModelSelect(e.target.value);
    });
}

function updateModelSelect(provider) {
    const modelSelect = document.getElementById('model');
    modelSelect.innerHTML = '<option value="">Select Model</option>';
    
    if (provider && modelsByProvider[provider]) {
        // Sort models by name
        const sortedModels = modelsByProvider[provider].sort((a, b) => a.name.localeCompare(b.name));
        
        sortedModels.forEach(model => {
            const option = document.createElement('option');
            option.value = model.id;
            option.text = model.name;
            option.title = `Context Length: ${model.contextLength || 'N/A'}\n${model.description || 'No description available'}`;
            option.selected = model.id === currentModel;
            
            // Add additional model information
            const contextInfo = model.contextLength ? ` (${model.contextLength.toLocaleString()} tokens)` : '';
            option.text = `${model.name}${contextInfo}`;
            
            modelSelect.add(option);
        });
    }
}

// Event listener for API key
document.getElementById('aiml_api_key').addEventListener('change', async () => {
    await initializeSelects();
});

// Initialization if there is an API key
if (document.getElementById('aiml_api_key').value) {
    initializeSelects();
}

function toggleApiKeyVisibility() {
    const input = document.getElementById('aiml_api_key');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeOffIcon = document.getElementById('eye-off-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.add('hidden');
        eyeOffIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('hidden');
        eyeOffIcon.classList.add('hidden');
    }
}
</script>
@endpush 