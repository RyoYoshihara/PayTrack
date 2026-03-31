<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\FundTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FundTransferService
{
    public function create(User $user, array $data): FundTransfer
    {
        return $user->fundTransfers()->create($data);
    }

    public function getForMonth(User $user, int $year, int $month): Collection
    {
        return $user->fundTransfers()
            ->with(['fromAccount', 'toAccount'])
            ->forMonth($year, $month)
            ->orderBy('scheduled_date')
            ->get();
    }

    public function find(int $id, User $user): ?FundTransfer
    {
        return $user->fundTransfers()
            ->with(['fromAccount', 'toAccount'])
            ->find($id);
    }

    public function confirm(FundTransfer $transfer, string $side): FundTransfer
    {
        return DB::transaction(function () use ($transfer, $side) {
            if ($side === 'from') {
                $transfer->from_confirmed = true;
            } elseif ($side === 'to') {
                $transfer->to_confirmed = true;
            }

            // 双方確認で自動完了 → 口座残高に反映
            if ($transfer->from_confirmed && $transfer->to_confirmed) {
                $transfer->status = 'completed';

                // 振出元: 残高を減額
                BankAccount::where('id', $transfer->from_account_id)
                    ->decrement('balance', $transfer->amount);
                // 振込先: 残高を増額
                BankAccount::where('id', $transfer->to_account_id)
                    ->increment('balance', $transfer->amount);
            }

            $transfer->save();
            return $transfer->fresh();
        });
    }

    public function cancel(FundTransfer $transfer): FundTransfer
    {
        $transfer->status = 'cancelled';
        $transfer->save();
        return $transfer->fresh();
    }
}
