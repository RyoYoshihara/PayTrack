@extends('layouts.app')
@section('page-title', 'ダッシュボード')

@section('content')
<div class="space-y-6">
    {{-- ヘッダー --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">月次サマリー</h2>
            <p class="text-sm text-gray-500 mt-1">収支の概要を確認できます</p>
        </div>
        <x-month-picker :year="$year" :month="$month" route="dashboard" />
    </div>

    {{-- 口座残高 --}}
    @if($bankAccounts->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800">口座残高</h3>
            <span class="text-lg font-bold text-gray-800">&yen;{{ number_format($bankAccounts->sum('balance')) }}</span>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($bankAccounts as $account)
            <div class="px-4 sm:px-6 py-3.5 flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $account->name }}</p>
                    <p class="text-xs text-gray-400">{{ $account->bank_name }}</p>
                </div>
                <p class="text-base font-bold ml-4 whitespace-nowrap {{ $account->balance >= 0 ? 'text-gray-800' : 'text-red-500' }}">&yen;{{ number_format($account->balance) }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 収支サマリーカード --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-500">収入合計</p>
            </div>
            <p class="text-2xl font-bold text-green-600">&yen;{{ number_format($summary['total_income']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-500">支出合計</p>
            </div>
            <p class="text-2xl font-bold text-red-500">&yen;{{ number_format($summary['total_expense']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-500">収支バランス</p>
            </div>
            <p class="text-2xl font-bold {{ $summary['balance'] >= 0 ? 'text-gray-800' : 'text-red-500' }}">&yen;{{ number_format($summary['balance']) }}</p>
        </div>
    </div>

    {{-- ステータス別件数 --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $statuses = [
                ['key' => 'scheduled', 'label' => '予定', 'color' => 'blue'],
                ['key' => 'completed', 'label' => '完了', 'color' => 'green'],
                ['key' => 'carried_over', 'label' => '繰越', 'color' => 'amber'],
                ['key' => 'cancelled', 'label' => '取消', 'color' => 'gray'],
            ];
        @endphp
        @foreach($statuses as $s)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ $s['label'] }}</p>
            <p class="text-2xl font-bold text-{{ $s['color'] }}-600">{{ $summary['status_summary'][$s['key']] }}</p>
        </div>
        @endforeach
    </div>

    {{-- 口座別サマリー --}}
    @if(count($accountSummaries) > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-800">口座別サマリー</h3>
        </div>
        <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">口座名</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">収入</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">支出</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">収支</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($accountSummaries as $account)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $account['account_name'] }}</td>
                    <td class="px-6 py-4 text-sm text-right text-green-600 font-medium">&yen;{{ number_format($account['total_income']) }}</td>
                    <td class="px-6 py-4 text-sm text-right text-red-500 font-medium">&yen;{{ number_format($account['total_expense']) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-semibold {{ $account['balance'] >= 0 ? 'text-gray-800' : 'text-red-500' }}">&yen;{{ number_format($account['balance']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- バッチ操作（当月のみ表示） --}}
    @if($year == now()->year && $month == now()->month)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">一括操作</h3>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('batch.generate') }}">
                @csrf
                <input type="hidden" name="target_month" value="{{ sprintf('%04d-%02d', $year, $month) }}">
                <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm"
                        onclick="return confirm('ルールをもとに今月の取引データを生成しますか？')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    今月のデータ生成
                </button>
            </form>
            <form method="POST" action="{{ route('batch.carry-over') }}">
                @csrf
                <input type="hidden" name="source_month" value="{{ sprintf('%04d-%02d', $year, $month) }}">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-xl transition shadow-sm"
                        onclick="return confirm('今月の未処理取引を繰り越しますか？')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    繰り越し処理
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
