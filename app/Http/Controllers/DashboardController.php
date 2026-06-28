<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        $stats = $this->reportService->getDashboardStats();

        if (Auth::user()->isAdmin()) {
            $active_users = \App\Models\User::where('is_active', true)
                ->orderByDesc('last_login_at')
                ->get();

            $adminStats = $this->reportService->getAdminDashboardStats();
            return view('dashboard.admin', array_merge($stats, $adminStats, [
                'active_users' => $active_users,
            ]));
        }

        return view('dashboard.staff', $stats);
    }
}
