<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\FundTransfer;
use App\Models\Transaction;
use App\Models\User;

class DashboardService
{
    /**
     * 月次サマリー（completedのみ集計）
     */
    public function getSummary(User $user, int $year, int $month): array
    {
        $baseQuery = $user->transactions()->forMonth($year, $month);

        $totalIncome = (clone $baseQuery)->completed()->income()->sum('amount');
        $totalExpense = (clone $baseQuery)->completed()->expense()->sum('amount');

        // ステータス別カウント
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'year' => $year,
            'month' => $month,
            'total_income' => (int) $totalIncome,
            'total_expense' => (int) $totalExpense,
            'balance' => (int) ($totalIncome - $totalExpense),
            'status_summary' => [
                'scheduled' => $statusCounts['scheduled'] ?? 0,
                'completed' => $statusCounts['completed'] ?? 0,
                'carried_over' => $statusCounts['carried_over'] ?? 0,
                'cancelled' => $statusCounts['cancelled'] ?? 0,
            ],
        ];
    }

    /**
     * 口座別サマリー（振替金額を含む）
     */
    public function getSummaryByAccount(User $user, int $year, int $month): array
    {
        $accounts = $user->bankAccounts()->ordered()->get();

        $result = [];
        foreach ($accounts as $account) {
            // 完了済みトランザクションの収入
            $income = Transaction::forUser($user->id)
                ->forMonth($year, $month)
                ->completed()
                ->income()
                ->where('bank_account_id', $account->id)
                ->sum('amount');

            // 完了済みトランザクションの支出
            $expense = Transaction::forUser($user->id)
                ->forMonth($year, $month)
                ->completed()
                ->expense()
                ->where('bank_account_id', $account->id)
                ->sum('amount');

            // 完了済み振替: この口座への入金
            $transferIn = FundTransfer::forUser($user->id)
                ->forMonth($year, $month)
                ->where('status', 'completed')
                ->where('to_account_id', $account->id)
                ->sum('amount');

            // 完了済み振替: この口座からの出金
            $transferOut = FundTransfer::forUser($user->id)
                ->forMonth($year, $month)
                ->where('status', 'completed')
                ->where('from_account_id', $account->id)
                ->sum('amount');

            $totalIncome = (int) ($income + $transferIn);
            $totalExpense = (int) ($expense + $transferOut);

            $result[] = [
                'account_id' => $account->id,
                'account_name' => $account->display_name,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'balance' => $totalIncome - $totalExpense,
            ];
        }

        return $result;
    }
}
