<?php

namespace App\Http\Controllers;

use App\Services\BatchService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function __construct(
        private BatchService $batchService
    ) {}

    public function generate(Request $request)
    {
        $request->validate([
            'target_month' => ['required', 'date_format:Y-m'],
        ]);

        $date = Carbon::createFromFormat('Y-m', $request->input('target_month'));
        $result = $this->batchService->generateMonthly($request->user(), $date->year, $date->month);

        return redirect()->back()->with('success',
            "データを生成しました（生成: {$result['generated_count']}件, スキップ: {$result['skipped_count']}件）"
        );
    }

    public function carryOver(Request $request)
    {
        $request->validate([
            'source_month' => ['required', 'date_format:Y-m'],
        ]);

        $date = Carbon::createFromFormat('Y-m', $request->input('source_month'));
        $count = $this->batchService->carryOver($request->user(), $date->year, $date->month);

        return redirect()->back()->with('success', "{$count}件の取引を繰り越しました。");
    }
}
