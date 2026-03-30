<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateStatusRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Services\BankAccountService;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService,
        private BankAccountService $bankAccountService
    ) {}

    public function index(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $type = $request->query('type');
        $status = $request->query('status');

        $transactions = $this->transactionService->getForMonth(
            $request->user(), $year, $month, $type, $status
        );

        return view('transactions.index', compact('year', 'month', 'type', 'status', 'transactions'));
    }

    public function create(Request $request)
    {
        $bankAccounts = $this->bankAccountService->getAll($request->user());
        return view('transactions.create', compact('bankAccounts'));
    }

    public function store(StoreTransactionRequest $request)
    {
        $this->transactionService->create($request->user(), $request->validated());
        return redirect()->route('transactions.index')->with('success', '取引を登録しました。');
    }

    public function edit(Request $request, Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        $bankAccounts = $this->bankAccountService->getAll($request->user());
        return view('transactions.edit', compact('transaction', 'bankAccounts'));
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        $this->transactionService->update($transaction, $request->validated());
        return redirect()->route('transactions.index')->with('success', '取引を更新しました。');
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

    public function destroy(Transaction $transaction)
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        $this->transactionService->delete($transaction);
        return redirect()->route('transactions.index')->with('success', '取引を削除しました。');
    }
}
