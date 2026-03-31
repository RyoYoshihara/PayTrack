<?php

namespace App\Http\Controllers;

use App\Services\BankAccountService;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private BankAccountService $bankAccountService
    ) {}

    public function index(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $user = $request->user();
        $summary = $this->dashboardService->getSummary($user, $year, $month);
        $accountSummaries = $this->dashboardService->getSummaryByAccount($user, $year, $month);

        $bankAccounts = $this->bankAccountService->getAll($user);

        return view('dashboard.index', compact('year', 'month', 'summary', 'accountSummaries', 'bankAccounts'));
    }
}
