<?php

namespace DashLog\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use Illuminate\Support\Facades\DB;
use DashLog\Application\Presenters\RequestLogPresenterInterface;
use DashLog\Domain\Entities\RequestLog;
use DashLog\Domain\ValueObjects\RequestMethod;
use DashLog\Domain\ValueObjects\RequestStatus;
use DateTimeImmutable;
use DashLog\Infrastructure\Http\Services\ErrorAnalyzer as ServicesErrorAnalyzer;

class DashLogController extends Controller
{
    public function __construct(
        private RequestLogRepositoryInterface $repository,
        private RequestLogPresenterInterface $presenter
    ) {}

    public function index()
    {
        // Get all logs for statistics
        $allLogs = DB::table('request_logs')->get();
        $allPresentedLogs = $this->presenter->presentCollection(
            collect($allLogs)->map(function ($log) {
                return new RequestLog(
                    id: $log->id,
                    method: RequestMethod::fromString($log->method),
                    url: $log->url,
                    ip: $log->ip,
                    userId: $log->user_id,
                    duration: $log->duration,
                    status: RequestStatus::fromStatusCode($log->status_code),
                    requestData: json_decode($log->request, true) ?? [],
                    responseData: json_decode($log->response, true) ?? [],
                    createdAt: new DateTimeImmutable($log->created_at),
                    userAgent: $log->user_agent,
                    headers: json_decode($log->headers, associative: true) ?? [],
                    cookies: json_decode($log->cookies, true) ?? [],
                    session: json_decode($log->session, true) ?? [],
                    stackTrace: json_decode($log->stack_trace, true) ?? [],
                );
            })->toArray()
        );

        // Calculate statistics using all logs
        $statusCodes = collect($allPresentedLogs)->groupBy(function ($log) {
            return $log['status']['code'];
        })->map->count();

        // Get paginated logs for the table
        $paginatedLogs = DB::table('request_logs')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Transform and present the paginated logs
        $presentedPaginatedLogs = $this->presenter->presentCollection(
            collect($paginatedLogs->items())->map(function ($log) {
                return new RequestLog(
                    id: $log->id,
                    method: RequestMethod::fromString($log->method),
                    url: $log->url,
                    ip: $log->ip,
                    userId: $log->user_id,
                    duration: $log->duration,
                    status: RequestStatus::fromStatusCode($log->status_code),
                    requestData: json_decode($log->request, true) ?? [],
                    responseData: json_decode($log->response, true) ?? [],
                    createdAt: new DateTimeImmutable($log->created_at),
                    userAgent: $log->user_agent,
                    headers: json_decode($log->headers, associative: true) ?? [],
                    cookies: json_decode($log->cookies, true) ?? [],
                    session: json_decode($log->session, true) ?? [],
                    stackTrace: json_decode($log->stack_trace, true) ?? [],
                );
            })->toArray()
        );

        // Create paginator with the presented logs
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $presentedPaginatedLogs,
            $paginatedLogs->total(),
            $paginatedLogs->perPage(),
            $paginatedLogs->currentPage(),
            ['path' => request()->url()]
        );

        $colors = [
            '200' => '#10B981',
            '300' => '#60A5FA',
            '400' => '#F59E0B',
            '500' => '#EF4444',
            'unknown' => '#6B7280'
        ];
        $stats = [
            'total_requests' => $paginatedLogs->total(),
            'avg_duration' => collect($presentedPaginatedLogs)->avg('duration.raw'),
            'success_rate' => $this->calculateSuccessRate($allPresentedLogs),
            'error_rate' => $this->calculateErrorRate($allPresentedLogs),
            'status_codes' => [
                'labels' => $statusCodes->keys()->toArray(),
                'data' => $statusCodes->values()->toArray(),
                'colors' => $statusCodes->keys()->map(function($status) use ($colors) {
                    return $colors[$status] ?? $colors['unknown'];
                })->toArray()
            ],
        ];

        return view('dashlog::dashboard', compact('logs', 'stats'));
    }

    private function calculateSuccessRate($logs)
    {
        $total = count($logs);
        if ($total === 0) return 0;
        
        $success = collect($logs)->filter(function ($log) {
            return $log['status']['code'] >= 200 && $log['status']['code'] < 300;
        })->count();
        
        return ($success / $total) * 100;
    }

    private function calculateErrorRate($logs)
    {
    $total = count($logs);
    if ($total === 0) return 0;
    
    $errors = collect($logs)->filter(function ($log) {
        return $log['status']['code'] >= 400;
    })->count();
    return ($errors / $total) * 100;
    }
    public function stats()
    {
        return response()->json($this->repository->getStats());
    }

    public function logs(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        
        return response()->json(
            $this->repository->getPaginatedLogs($page, $perPage)
        );
    }

    public function show(string $id)
    {
        $log = $this->repository->findById($id);
        
        if (!$log) {
            abort(404);
        }
        return view('dashlog::show', [
            'log' => $this->presenter->present($log),
        ]);
    }

    private function generateChartColors($count)
    {
        $colors = [
            '#10B981', // green for 2xx
            '#3B82F6', // blue for 3xx
            '#F59E0B', // yellow for 4xx
            '#EF4444', // red for 5xx
        ];

        return collect(range(0, $count - 1))->map(function ($i) use ($colors) {
            $baseColor = $colors[$i % count($colors)];
            return $i < count($colors) ? $baseColor : $this->adjustColor($baseColor, $i);
        })->toArray();
    }

    private function adjustColor($hex, $index)
    {
        // Adjust the base color to create variations
        $rgb = sscanf($hex, "#%02x%02x%02x");
        $adjustment = ($index * 20) % 100 - 50;
        
        return sprintf("#%02x%02x%02x",
            max(0, min(255, $rgb[0] + $adjustment)),
            max(0, min(255, $rgb[1] + $adjustment)),
            max(0, min(255, $rgb[2] + $adjustment))
        );
    }

    public function analyze($id)
    {
        $log = $this->repository->findById($id);
        if (!$log) {
            return response()->json(data: ['error' => 'Log not found'], status: 404);
        }

        $presenter = app(RequestLogPresenterInterface::class);
        $presentedLog = $presenter->present($log);
        
        $analyzer = app(ServicesErrorAnalyzer::class);
        $analysis = $analyzer->analyze($presentedLog);

        return response()->json($analysis);
    }
} 