<?php

namespace App\Http\Controllers;

use App\Http\Requests\Rule\StoreRuleRequest;
use App\Http\Requests\Rule\UpdateRuleRequest;
use App\Models\TransactionRule;
use App\Services\BankAccountService;
use App\Services\RuleService;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    public function __construct(
        private RuleService $ruleService,
        private BankAccountService $bankAccountService
    ) {}

    public function index(Request $request)
    {
        $rules = $this->ruleService->getAll($request->user());
        return view('rules.index', compact('rules'));
    }

    public function create(Request $request)
    {
        $bankAccounts = $this->bankAccountService->getAll($request->user());
        return view('rules.create', compact('bankAccounts'));
    }

    public function store(StoreRuleRequest $request)
    {
        $this->ruleService->create($request->user(), $request->validated());
        return redirect()->route('rules.index')->with('success', 'ルールを登録しました。');
    }

    public function edit(Request $request, TransactionRule $rule)
    {
        abort_unless($rule->user_id === auth()->id(), 403);

        $bankAccounts = $this->bankAccountService->getAll($request->user());
        return view('rules.edit', compact('rule', 'bankAccounts'));
    }

    public function update(UpdateRuleRequest $request, TransactionRule $rule)
    {
        abort_unless($rule->user_id === auth()->id(), 403);

        $this->ruleService->update($rule, $request->validated());
        return redirect()->route('rules.index')->with('success', 'ルールを更新しました。');
    }

    public function destroy(TransactionRule $rule)
    {
        abort_unless($rule->user_id === auth()->id(), 403);

        $this->ruleService->delete($rule);
        return redirect()->route('rules.index')->with('success', 'ルールを削除しました。');
    }
}
