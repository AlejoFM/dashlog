@extends('dashlog::layouts.app')

@section('title', 'Request Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Request Details
            </h3>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Method</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $log['method'] }}</dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">URL</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $log['url'] }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $log['status']['code'] >= 400 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $log['status']['code'] }}
                        </span>
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Duration</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $log['duration']['formatted'] }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $log['timestamp']['formatted'] }}</dd>
                </div>

                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $log['user_agent'] ?? 'N/A' }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">User ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $log['user_id'] ?? 'N/A' }}</dd>
                </div>
                {{-- Request Body (conditional) --}}
                @if(config('dashlog.logging.fields.request_body'))
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Request Data</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto">{{ json_encode($log['request'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </dd>
                </div>
                @endif

                {{-- Response Body (conditional) --}}
                @if(config('dashlog.logging.fields.response_body'))
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Response Data</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto">{{ json_encode($log['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </dd>
                </div>
                @endif
                {{-- Headers (conditional) --}}
                @if(config('dashlog.logging.fields.headers'))
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Headers</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto">{{ json_encode($log['headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </dd>
                </div>
                @endif

                {{-- Cookies (conditional) --}}
                @if(config('dashlog.logging.fields.cookies'))
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Cookies</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto">{{ json_encode($log['cookies'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </dd>
                </div>
                @endif

                {{-- Session (conditional) --}}
                @if(config('dashlog.logging.fields.session'))
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Session Data</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto">{{ json_encode($log['session'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </dd>
                </div>
                @endif
                {{-- Stack Trace (conditional) --}}
                @if(config('dashlog.logging.fields.stack_trace') && isset($log['details']['error']))
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Stack Trace</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <pre class="bg-gray-100 p-4 rounded overflow-x-auto">{{ json_encode($log['details']['error'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </dd>
                </div>
                @endif

                {{-- IA ERROR ANALYSIS --}}
                @if(isset($log['error_analysis']))
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">AI Analysis</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="prose prose-sm max-w-none">
                                {{ $log['error_analysis']['explanation'] }}
                            </div>
                            <div class="mt-2 flex items-center">
                                <span class="text-xs text-gray-500">Confidence:</span>
                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $log['error_analysis']['confidence_level'] === 'high' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($log['error_analysis']['confidence_level']) }}
                                </span>
                            </div>
                        </div>
                    </dd>
                </div>
                @endif

                @if(isset($log['details']['error']))
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Error Analysis</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div id="analysis-container" class="bg-gray-50 rounded-lg p-4">
                                <button 
                                    onclick="analyzeError('{{ $log['id'] }}')" 
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                                    id="analyze-button"
                                >
                                    Analyze Error
                                </button>
                                <div id="analysis-result" class="mt-4 hidden">
                                    <div class="animate-pulse" id="loading-indicator">
                                        Analyzing error...
                                    </div>
                                    <div id="analysis-content" class="prose prose-sm"></div>
                                </div>
                            </div>
                        </dd>
                    </div>
                    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
                    <script>
                    function analyzeError(logId) {
                        const button = document.getElementById('analyze-button');
                        const loading = document.getElementById('loading-indicator');
                        const result = document.getElementById('analysis-result');
                        const content = document.getElementById('analysis-content');
                        
                        button.disabled = true;
                        button.classList.add('hidden');
                        result.classList.remove('hidden');
                        loading.classList.remove('hidden');
                        content.classList.add('hidden');

                        fetch(`/dashlog/logs/${logId}/analyze`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            loading.classList.add('hidden');
                            content.classList.remove('hidden');
                            content.innerHTML = marked.parse(data.explanation);
                        })
                        .catch(error => {
                            loading.classList.add('hidden');
                            content.classList.remove('hidden');
                            content.innerHTML = 'Error analyzing the log. Please try again.';
                        })
                        .finally(() => {
                            button.disabled = false;
                        });
                    }
                    </script>
                @endif
            </dl>
        </div>
    </div>
</div>

@push('scripts')
<script>
function analyze(id) {
    const result = document.getElementById('result');
    const loading = document.getElementById('loading');
    const content = document.getElementById('content');
    
    if (!result || !loading || !content) {
        console.error('Required elements not found');
        return;
    }
    
    result.classList.remove('hidden');
    loading.style.display = 'block';
    content.style.display = 'none';
    
    fetch(`/dashlog/logs/${id}/analyze`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        console.log(data);
        loading.style.display = 'none';
        content.style.display = 'block';
        // Convert markdown to HTML
        content.innerHTML = marked.parse(data.explanation);
    })
    .catch(err => {
        loading.style.display = 'none';
        content.style.display = 'block';
        content.innerHTML = 'Error analyzing log';
        console.error(err);
    });
}
</script>
@endpush
@endsection