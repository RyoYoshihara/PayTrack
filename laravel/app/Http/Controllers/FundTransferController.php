<?php

namespace App\Http\Controllers;

use App\Http\Requests\FundTransfer\ConfirmFundTransferRequest;
use App\Http\Requests\FundTransfer\StoreFundTransferRequest;
use App\Models\FundTransfer;
use App\Services\BankAccountService;
use App\Services\FundTransferService;
use Illuminate\Http\Request;

class FundTransferController extends Controller
{
    public function __construct(
        private FundTransferService $fundTransferService,
        private BankAccountService $bankAccountService
    ) {}

    public function index(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        $transfers = $this->fundTransferService->getForMonth($request->user(), $year, $month);
        $bankAccounts = $this->bankAccountService->getAll($request->user());

        return view('fund-transfers.index', compact('year', 'month', 'transfers', 'bankAccounts'));
    }

    public function create(Request $request)
    {
        $bankAccounts = $this->bankAccountService->getAll($request->user());
        return view('fund-transfers.create', compact('bankAccounts'));
    }

    public function store(StoreFundTransferRequest $request)
    {
        $this->fundTransferService->create($request->user(), $request->validated());
        return redirect()->route('fund-transfers.index')->with('success', '振替を登録しました。');
    }

    public function confirm(ConfirmFundTransferRequest $request, FundTransfer $fundTransfer)
    {
        abort_unless($fundTransfer->user_id === auth()->id(), 403);
        abort_if($fundTransfer->status !== 'scheduled', 400, 'この振替は確認できません。');

        $this->fundTransferService->confirm($fundTransfer, $request->validated()['side']);
        return redirect()->back()->with('success', '確認しました。');
    }

    public function cancel(FundTransfer $fundTransfer)
    {
        abort_unless($fundTransfer->user_id === auth()->id(), 403);
        abort_if($fundTransfer->status !== 'scheduled', 400, 'この振替はキャンセルできません。');

        $this->fundTransferService->cancel($fundTransfer);
        return redirect()->back()->with('success', '振替をキャンセルしました。');
    }
}
