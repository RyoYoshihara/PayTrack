@extends('layouts.app')
@section('page-title', '取引一覧')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">取引一覧</h2>
            <p class="text-sm text-gray-500 mt-1">すべての取引を管理できます</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <x-month-picker :year="$year" :month="$month" route="transactions.index" />
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                新規登録
            </a>
        </div>
    </div>

    {{-- フィルター --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <select name="type" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2">
                <option value="">すべてのタイプ</option>
                <option value="income" {{ $type === 'income' ? 'selected' : '' }}>収入</option>
                <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>支出</option>
            </select>
            <select name="status" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-2">
                <option value="">すべての状態</option>
                <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>予定</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>完了</option>
                <option value="carried_over" {{ $status === 'carried_over' ? 'selected' : '' }}>繰越</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>取消</option>
            </select>
        </form>
    </div>

    {{-- テーブル --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">タイトル</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">タイプ</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">金額</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">予定日</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">実績日</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">状態</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-800 whitespace-nowrap">{{ $txn->title }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $txn->type === 'income' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                {{ $txn->type === 'income' ? '収入' : '支出' }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-right font-medium whitespace-nowrap {{ $txn->type === 'income' ? 'text-green-600' : 'text-red-500' }}">&yen;{{ number_format($txn->amount) }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $txn->scheduled_date->format('m/d') }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $txn->actual_date?->format('m/d') ?? '-' }}</td>
                        <td class="px-4 sm:px-6 py-4 text-center"><x-status-badge :status="$txn->status" /></td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <div class="flex justify-center gap-1 flex-wrap">
                                <a href="{{ route('transactions.edit', $txn) }}" class="px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">編集</a>
                                @if($txn->isEditable())
                                <form method="POST" action="{{ route('transactions.update-status', $txn) }}"
                                      onsubmit="return checkBalanceBeforeComplete(this, {{ $txn->bank_account_id ?? 'null' }}, {{ $txn->amount }}, '{{ $txn->type }}')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <input type="hidden" name="actual_date" value="{{ now()->toDateString() }}">
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition">完了</button>
                                </form>
                                <form method="POST" action="{{ route('transactions.destroy', $txn) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" onclick="return confirm('この取引を削除しますか？')">削除</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-10 text-sm text-gray-400 text-center">取引データがありません</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $transactions->appends(request()->query())->links() }}</div>
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
