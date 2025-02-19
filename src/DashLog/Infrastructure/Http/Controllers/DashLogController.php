<?php

namespace DashLog\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use Illuminate\Support\Facades\DB;
use DashLog\Application\Presenters\RequestLogPresenterInterface;

class DashLogController extends Controller
{
    public function __construct(
        private RequestLogRepositoryInterface $repository,
        private RequestLogPresenterInterface $presenter
    ) {}

    public function index()
    {
        $logs = $this->repository->getPaginatedLogs(1, 15);
        $stats = $this->repository->getStats();

        return view('dashlog::dashboard', [
            'logs' => $this->presenter->presentCollection($logs),
            'stats' => $this->presenter->presentStats($stats),
        ]);
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
} 