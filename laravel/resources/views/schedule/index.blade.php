@extends('layouts.app')
@section('page-title', '収支スケジュール')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">収支スケジュール</h2>
            <p class="text-sm text-gray-500 mt-1">予定・繰越中の取引を管理できます</p>
        </div>
        <x-month-picker :year="$year" :month="$month" route="schedule" />
    </div>

    @php
        $sections = [
            ['title' => '収入', 'transactions' => $incomeTransactions, 'colorText' => 'text-green-600', 'colorBg' => 'bg-green-50', 'colorBorder' => 'border-green-200'],
            ['title' => '支出', 'transactions' => $expenseTransactions, 'colorText' => 'text-red-500', 'colorBg' => 'bg-red-50', 'colorBorder' => 'border-red-200'],
        ];
    @endphp

    @foreach($sections as $section)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-semibold {{ $section['colorText'] }}">{{ $section['title'] }}</h3>
            <span class="text-base sm:text-lg font-bold {{ $section['colorText'] }}">&yen;{{ number_format($section['transactions']->sum('amount')) }}</span>
        </div>
        @if($section['transactions']->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">タイトル</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">金額</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">予定日</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">状態</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($section['transactions'] as $txn)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-800">{{ $txn->title }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-right font-medium {{ $section['colorText'] }}">&yen;{{ number_format($txn->amount) }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500">{{ $txn->scheduled_date->format('m/d') }}</td>
                        <td class="px-4 sm:px-6 py-4 text-center"><x-status-badge :status="$txn->status" /></td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <div class="flex justify-center gap-1 flex-wrap">
                                <form method="POST" action="{{ route('schedule.update-status', $txn) }}"
                                      onsubmit="return checkBalanceBeforeComplete(this, {{ $txn->bank_account_id ?? 'null' }}, {{ $txn->amount }}, '{{ $txn->type }}')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <input type="hidden" name="actual_date" value="{{ now()->toDateString() }}">
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">完了</button>
                                </form>
                                <form method="POST" action="{{ route('schedule.update-status', $txn) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="carried_over">
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-100 transition">繰越</button>
                                </form>
                                <form method="POST" action="{{ route('schedule.update-status', $txn) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition" onclick="return confirm('この取引を取消しますか？')">取消</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-10 text-center">
            <p class="text-sm text-gray-400">対象の取引はありません</p>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
const accountBalances = @json($bankAccounts->pluck('balance', 'id'));

function checkBalanceBeforeComplete(form, accountId, amount, type) {
    if (type === 'expense' && accountId && accountBalances[accountId] !== undefined) {
        if (amount > accountBalances[accountId]) {
            const balanceFormatted = Number(accountBalances[accountId]).toLocaleString();
            if (!confirm(`この支出（¥${Number(amount).toLocaleString()}）は口座残高（¥${balanceFormatted}）を超えています。\n\n処理を続行しますか？`)) {
                return false;
            }
        }
    }
    return true;
}
</script>
@endpush
