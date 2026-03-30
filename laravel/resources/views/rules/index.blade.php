@extends('layouts.app')
@section('page-title', 'ルール設定')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">ルール設定</h2>
            <p class="text-sm text-gray-500 mt-1">定期的な収支ルールを管理します</p>
        </div>
        <a href="{{ route('rules.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm self-start sm:self-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            新規登録
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">タイトル</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">タイプ</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">金額</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">繰返し</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">日</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">適用期間</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rules as $rule)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-800 whitespace-nowrap">{{ $rule->title }}</td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $rule->type === 'income' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                {{ $rule->type === 'income' ? '収入' : '支出' }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-right font-medium text-gray-800 whitespace-nowrap">&yen;{{ number_format($rule->amount) }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-center text-gray-600">{{ $rule->recurrence === 'monthly' ? '毎月' : '一回のみ' }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-center text-gray-600">{{ $rule->day_of_month ? $rule->day_of_month.'日' : '-' }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $rule->start_month_display ?? '-' }} ～ {{ $rule->end_month_display ?? '-' }}</td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <div class="flex justify-center gap-1">
                                <a href="{{ route('rules.edit', $rule) }}" class="px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">編集</a>
                                <form method="POST" action="{{ route('rules.destroy', $rule) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" onclick="return confirm('このルールを削除しますか？')">削除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-10 text-sm text-gray-400 text-center">ルールが登録されていません</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $rules->links() }}</div>
</div>
@endsection
