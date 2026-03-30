<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionRule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RuleService
{
    /**
     * ルール作成＋初回トランザクション自動生成
     */
    public function create(User $user, array $data): TransactionRule
    {
        return DB::transaction(function () use ($user, $data) {
            // start_month, end_monthのパース
            if (isset($data['start_month']) && is_string($data['start_month'])) {
                $data['start_month'] = Carbon::createFromFormat('Y-m', $data['start_month'])->startOfMonth()->toDateString();
            }
            if (isset($data['end_month']) && is_string($data['end_month'])) {
                $data['end_month'] = Carbon::createFromFormat('Y-m', $data['end_month'])->startOfMonth()->toDateString();
            }

            $rule = $user->transactionRules()->create($data);

            $today = Carbon::today();
            $currentMonthFirst = $today->copy()->startOfMonth();

            if ($rule->recurrence === 'monthly') {
                // 当月分を自動生成（日付範囲内の場合）
                $inRange = true;
                if ($rule->start_month && $currentMonthFirst->lt($rule->start_month)) {
                    $inRange = false;
                }
                if ($rule->end_month && $currentMonthFirst->gt($rule->end_month)) {
                    $inRange = false;
                }

                if ($inRange && $rule->day_of_month) {
                    $maxDay = Carbon::create($today->year, $today->month, 1)->daysInMonth;
                    $day = min($rule->day_of_month, $maxDay);

                    Transaction::create([
                        'user_id' => $user->id,
                        'rule_id' => $rule->id,
                        'bank_account_id' => $rule->bank_account_id,
                        'title' => $rule->title,
                        'amount' => $rule->amount,
                        'type' => $rule->type,
                        'scheduled_date' => Carbon::create($today->year, $today->month, $day)->toDateString(),
                        'memo' => $rule->memo,
                    ]);
                }
            } elseif ($rule->recurrence === 'once') {
                // 1件のトランザクションを生成
                if ($rule->start_month && $rule->day_of_month) {
                    $startMonth = Carbon::parse($rule->start_month);
                    $maxDay = Carbon::create($startMonth->year, $startMonth->month, 1)->daysInMonth;
                    $day = min($rule->day_of_month, $maxDay);
                    $scheduledDate = Carbon::create($startMonth->year, $startMonth->month, $day)->toDateString();
                } elseif ($rule->start_month) {
                    $scheduledDate = $rule->start_month;
                } else {
                    $scheduledDate = $today->toDateString();
                }

                Transaction::create([
                    'user_id' => $user->id,
                    'rule_id' => $rule->id,
                    'bank_account_id' => $rule->bank_account_id,
                    'title' => $rule->title,
                    'amount' => $rule->amount,
                    'type' => $rule->type,
                    'scheduled_date' => $scheduledDate,
                    'memo' => $rule->memo,
                ]);
            }

            return $rule->fresh();
        });
    }

    public function getAll(
        User $user,
        ?string $type = null,
        ?string $recurrence = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = $user->transactionRules()->latest();

        if ($type) {
            $query->where('type', $type);
        }
        if ($recurrence) {
            $query->where('recurrence', $recurrence);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id, User $user): ?TransactionRule
    {
        return $user->transactionRules()->find($id);
    }

    public function update(TransactionRule $rule, array $data): TransactionRule
    {
        // start_month, end_monthのパース
        if (isset($data['start_month']) && is_string($data['start_month'])) {
            $data['start_month'] = Carbon::createFromFormat('Y-m', $data['start_month'])->startOfMonth()->toDateString();
        }
        if (isset($data['end_month']) && is_string($data['end_month'])) {
            $data['end_month'] = Carbon::createFromFormat('Y-m', $data['end_month'])->startOfMonth()->toDateString();
        }

        $rule->update($data);
        return $rule->fresh();
    }

    public function delete(TransactionRule $rule): void
    {
        $rule->delete();
    }
}
