<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function create(User $user, array $data): Transaction
    {
        return $user->transactions()->create($data);
    }

    public function getForMonth(
        User $user,
        int $year,
        int $month,
        ?string $type = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = $user->transactions()
            ->forMonth($year, $month)
            ->orderBy('scheduled_date');

        if ($type) {
            $query->where('type', $type);
        }
        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id, User $user): ?Transaction
    {
        return $user->transactions()->find($id);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        return $transaction->fresh();
    }

    public function updateStatus(Transaction $transaction, string $newStatus, ?string $actualDate = null): Transaction
    {
        if (!$transaction->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "ステータス '{$transaction->status}' から '{$newStatus}' への遷移はできません。"
            );
        }

        return DB::transaction(function () use ($transaction, $newStatus, $actualDate) {
            $transaction->status = $newStatus;

            if ($newStatus === 'completed' && $actualDate) {
                $transaction->actual_date = $actualDate;
            } elseif ($newStatus === 'carried_over') {
                $this->createCarriedOverTransaction($transaction);
            }

            $transaction->save();
            return $transaction->fresh();
        });
    }

    public function delete(Transaction $transaction): void
    {
        $transaction->delete();
    }

    /**
     * 繰り越しトランザクションを翌月に作成
     */
    private function createCarriedOverTransaction(Transaction $transaction): void
    {
        $srcDate = $transaction->scheduled_date;
        $srcYear = $srcDate->year;
        $srcMonth = $srcDate->month;

        if ($srcMonth == 12) {
            $nextYear = $srcYear + 1;
            $nextMonth = 1;
        } else {
            $nextYear = $srcYear;
            $nextMonth = $srcMonth + 1;
        }

        $maxDay = Carbon::create($nextYear, $nextMonth, 1)->daysInMonth;
        $day = min($srcDate->day, $maxDay);

        Transaction::create([
            'user_id' => $transaction->user_id,
            'rule_id' => $transaction->rule_id,
            'bank_account_id' => $transaction->bank_account_id,
            'title' => $transaction->title,
            'amount' => $transaction->amount,
            'type' => $transaction->type,
            'scheduled_date' => Carbon::create($nextYear, $nextMonth, $day)->toDateString(),
            'carried_over_from' => $transaction->id,
            'memo' => $transaction->memo,
        ]);
    }
}
