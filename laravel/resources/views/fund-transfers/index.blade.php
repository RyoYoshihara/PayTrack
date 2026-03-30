@extends('layouts.app')
@section('page-title', '口座振替')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">口座振替</h2>
            <p class="text-sm text-gray-500 mt-1">口座間の振替を管理します</p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <x-month-picker :year="$year" :month="$month" route="fund-transfers.index" />
            <a href="{{ route('fund-transfers.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                新規登録
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">予定日</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">振出元</th>
                        <th class="px-2 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider"></th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">振込先</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">金額</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">状態</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transfers as $ft)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $ft->scheduled_date->format('m/d') }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-800 whitespace-nowrap">
                            {{ $ft->fromAccount?->display_name ?? '-' }}
                            @if($ft->from_confirmed)<span class="ml-1 text-green-500">&#10003;</span>@endif
                        </td>
                        <td class="px-2 py-4 text-center text-gray-300">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-800 whitespace-nowrap">
                            {{ $ft->toAccount?->display_name ?? '-' }}
                            @if($ft->to_confirmed)<span class="ml-1 text-green-500">&#10003;</span>@endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-right font-medium text-gray-800 whitespace-nowrap">&yen;{{ number_format($ft->amount) }}</td>
                        <td class="px-4 sm:px-6 py-4 text-center"><x-status-badge :status="$ft->status" /></td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            @if($ft->status === 'scheduled')
                            <div class="flex justify-center gap-1 flex-wrap">
                                @if(!$ft->from_confirmed)
                                <form method="POST" action="{{ route('fund-transfers.confirm', $ft) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="side" value="from">
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition whitespace-nowrap">振出確認</button>
                                </form>
                                @endif
                                @if(!$ft->to_confirmed)
                                <form method="POST" action="{{ route('fund-transfers.confirm', $ft) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="side" value="to">
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition whitespace-nowrap">振込確認</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('fund-transfers.cancel', $ft) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition" onclick="return confirm('この振替をキャンセルしますか？')">取消</button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-10 text-sm text-gray-400 text-center">振替データがありません</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
