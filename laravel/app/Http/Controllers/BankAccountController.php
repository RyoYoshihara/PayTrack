<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankAccount\ReorderRequest;
use App\Http\Requests\BankAccount\StoreBankAccountRequest;
use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function __construct(
        private BankAccountService $bankAccountService
    ) {}

    public function index(Request $request)
    {
        $bankAccounts = $this->bankAccountService->getAll($request->user());
        return view('bank-accounts.index', compact('bankAccounts'));
    }

    public function create()
    {
        return view('bank-accounts.create');
    }

    public function store(StoreBankAccountRequest $request)
    {
        $data = $request->validated();
        $this->bankAccountService->create($request->user(), $data['name'], $data['bank_name']);
        return redirect()->route('bank-accounts.index')->with('success', '口座を登録しました。');
    }

    public function edit(BankAccount $bankAccount)
    {
        abort_unless($bankAccount->user_id === auth()->id(), 403);

        return view('bank-accounts.edit', compact('bankAccount'));
    }

    public function update(StoreBankAccountRequest $request, BankAccount $bankAccount)
    {
        abort_unless($bankAccount->user_id === auth()->id(), 403);

        $this->bankAccountService->update($bankAccount, $request->validated());
        return redirect()->route('bank-accounts.index')->with('success', '口座を更新しました。');
    }

    public function destroy(BankAccount $bankAccount)
    {
        abort_unless($bankAccount->user_id === auth()->id(), 403);

        $this->bankAccountService->delete($bankAccount);
        return redirect()->route('bank-accounts.index')->with('success', '口座を削除しました。');
    }

    public function reorder(ReorderRequest $request)
    {
        $this->bankAccountService->reorder($request->user(), $request->validated()['ids']);
        return redirect()->route('bank-accounts.index')->with('success', '並び順を更新しました。');
    }
}
