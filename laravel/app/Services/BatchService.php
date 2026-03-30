<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BatchService
{
    /**
     * 月次一括生成（ルールからトランザクションを生成）
     */
    public function generateMonthly(User $user, int $year, int $month): array
    {
        return DB::transaction(function () use ($user, $year, $month) {
            $rules = $user->transactionRules()
                ->where('recurrence', 'monthly')
                ->get();

            $generated = 0;
            $skipped = 0;
            $targetFirst = Carbon::create($year, $month, 1)->startOfDay();

            foreach ($rules as $rule) {
                // 日付範囲チェック
                if ($rule->start_month && $targetFirst->lt($rule->start_month)) {
                    $skipped++;
                    continue;
                }
                if ($rule->end_month && $targetFirst->gt($rule->end_month)) {
                    $skipped++;
                    continue;
                }

                // 重複チェック
                $exists = Transaction::where('rule_id', $rule->id)
                    ->whereYear('scheduled_date', $year)
                    ->whereMonth('scheduled_date', $month)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $maxDay = Carbon::create($year, $month, 1)->daysInMonth;
                $day = min($rule->day_of_month, $maxDay);

                Transaction::create([
                    'user_id' => $user->id,
                    'rule_id' => $rule->id,
                    'bank_account_id' => $rule->bank_account_id,
                    'title' => $rule->title,
                    'amount' => $rule->amount,
                    'type' => $rule->type,
                    'scheduled_date' => Carbon::create($year, $month, $day)->toDateString(),
                    'memo' => $rule->memo,
                ]);
                $generated++;
            }

            return ['generated_count' => $generated, 'skipped_count' => $skipped];
        });
    }

    /**
     * 繰り越し処理（指定月のscheduledを翌月へ）
     */
    public function carryOver(User $user, int $year, int $month): int
    {
        return DB::transaction(function () use ($user, $year, $month) {
            $transactions = $user->transactions()
                ->forMonth($year, $month)
                ->scheduled()
                ->get();

            if ($month == 12) {
                $nextYear = $year + 1;
                $nextMonth = 1;
            } else {
                $nextYear = $year;
                $nextMonth = $month + 1;
            }

            $count = 0;
            foreach ($transactions as $txn) {
                $txn->status = 'carried_over';
                $txn->save();

                $maxDay = Carbon::create($nextYear, $nextMonth, 1)->daysInMonth;
                $day = min($txn->scheduled_date->day, $maxDay);

                Transaction::create([
                    'user_id' => $user->id,
                    'rule_id' => $txn->rule_id,
                    'bank_account_id' => $txn->bank_account_id,
                    'title' => $txn->title,
                    'amount' => $txn->amount,
                    'type' => $txn->type,
                    'scheduled_date' => Carbon::create($nextYear, $nextMonth, $day)->toDateString(),
                    'carried_over_from' => $txn->id,
                    'memo' => $txn->memo,
                ]);
                $count++;
            }

            return $count;
        });
    }
}
