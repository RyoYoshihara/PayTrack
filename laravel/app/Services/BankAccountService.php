<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BankAccountService
{
    public function create(User $user, string $name, string $bankName): BankAccount
    {
        $maxOrder = $user->bankAccounts()->max('sort_order') ?? -1;

        return $user->bankAccounts()->create([
            'name' => $name,
            'bank_name' => $bankName,
            'sort_order' => $maxOrder + 1,
        ]);
    }

    public function getAll(User $user): Collection
    {
        return $user->bankAccounts()->ordered()->get();
    }

    public function find(int $id, User $user): ?BankAccount
    {
        return $user->bankAccounts()->find($id);
    }

    public function update(BankAccount $account, array $data): BankAccount
    {
        $account->update($data);
        return $account->fresh();
    }

    public function delete(BankAccount $account): void
    {
        $account->delete();
    }

    public function reorder(User $user, array $ids): void
    {
        foreach ($ids as $index => $id) {
            BankAccount::where('id', $id)
                ->where('user_id', $user->id)
                ->update(['sort_order' => $index]);
        }
    }
}
