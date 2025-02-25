@extends('dashlog::layouts.app')

@section('title', 'Dashboard')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.addEventListener('load', function() {
    try {
        const ctx = document.getElementById('statusCodeChart');
        if (!ctx) {
            console.error('Canvas element not found');
            return;
        }

        const chartData = {
            labels: <?= json_encode($stats['status_codes']['labels']) ?>,
            data: <?= json_encode($stats['status_codes']['data']) ?>,
            colors: <?= json_encode($stats['status_codes']['colors']) ?>
        };

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.data,
                    backgroundColor: chartData.colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } catch (error) {
        console.error('Error creating chart:', error);
    }
});
</script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Total Requests</h3>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_requests'] ?? 0 }}</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Average Duration</h3>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['avg_duration'] ?? 0, 2) }}ms</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Success Rate</h3>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['success_rate'] ?? 0, 1) }}%</p>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Error Rate</h3>
            <p class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['error_rate'] ?? 0, 1) }}%</p>
        </div>
    </div>

    <!-- Graph of Status Codes -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900">Status Code Distribution</h2>
            <div class="mt-4" style="height: 300px;">
                <canvas id="statusCodeChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900">Recent Requests</h2>
            <div class="mt-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 bg-gray-50"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log['timestamp']['formatted'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($log['method']->value === 'GET')
                                        bg-green-100 text-green-800
                                    @elseif($log['method']->value === 'PUT') 
                                        bg-blue-100 text-blue-800
                                    @elseif($log['method']->value === 'POST')
                                        bg-yellow-100 text-yellow-800
                                    @elseif($log['method']->value === 'DELETE')
                                        bg-red-100 text-red-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $log['method'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ Str::limit($log['url'], 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $log['status']['code'] >= '400' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $log['status']['code'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log['duration']['formatted'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('dashlog.logs.show', $log['id']) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                No requests logged yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 flex justify-end w-full bg-white border-t border-gray-200">
                    <div class="flex items-center justify-end w-full">
                        {{ $logs->links('dashlog::components.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 