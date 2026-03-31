<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\UpdateStatusRequest;
use App\Models\Transaction;
use App\Services\BankAccountService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private BankAccountService $bankAccountService
    ) {}

    public function index(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $user = $request->user();

        $incomeTransactions = $user->transactions()
            ->forMonth($year, $month)
            ->where(function ($q) {
                $q->where('status', 'scheduled')->orWhere('status', 'carried_over');
            })
            ->income()
            ->orderBy('scheduled_date')
            ->get();

        $expenseTransactions = $user->transactions()
            ->forMonth($year, $month)
            ->where(function ($q) {
                $q->where('status', 'scheduled')->orWhere('status', 'carried_over');
            })
            ->expense()
            ->orderBy('scheduled_date')
            ->get();

        $bankAccounts = $this->bankAccountService->getAll($user);

        return view('schedule.index', compact(
            'year', 'month', 'incomeTransactions', 'expenseTransactions', 'bankAccounts'
        ));
    }

    public function updateStatus(UpdateStatusRequest $request, Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        try {
            $this->transactionService->updateStatus(
                $transaction,
                $request->validated()['status'],
                $request->validated()['actual_date'] ?? null
            );
            return redirect()->back()->with('success', 'ステータスを更新しました。');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
