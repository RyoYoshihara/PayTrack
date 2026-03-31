@extends('layouts.app')
@section('page-title', '口座管理')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800">口座管理</h2>
            <p class="text-sm text-gray-500 mt-1">銀行口座を管理できます</p>
        </div>
        <a href="{{ route('bank-accounts.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm self-start sm:self-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            新規登録
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">口座名</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">銀行名</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">残高</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">並び順</th>
                        <th class="px-4 sm:px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bankAccounts as $index => $account)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 sm:px-6 py-4 text-sm font-medium text-gray-800">{{ $account->name }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-gray-500">{{ $account->bank_name }}</td>
                        <td class="px-4 sm:px-6 py-4 text-sm text-right font-medium {{ $account->balance >= 0 ? 'text-gray-800' : 'text-red-500' }}">&yen;{{ number_format($account->balance) }}</td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <div class="flex justify-center gap-1">
                                @if($index > 0)
                                <form method="POST" action="{{ route('bank-accounts.reorder') }}">
                                    @csrf @method('PUT')
                                    @php $ids = $bankAccounts->pluck('id')->toArray(); $s = $ids; [$s[$index], $s[$index-1]] = [$s[$index-1], $s[$index]]; @endphp
                                    @foreach($s as $id)<input type="hidden" name="ids[]" value="{{ $id }}">@endforeach
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center bg-gray-50 rounded-lg hover:bg-gray-100 transition text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                </form>
                                @endif
                                @if($index < $bankAccounts->count() - 1)
                                <form method="POST" action="{{ route('bank-accounts.reorder') }}">
                                    @csrf @method('PUT')
                                    @php $ids = $bankAccounts->pluck('id')->toArray(); $s = $ids; [$s[$index], $s[$index+1]] = [$s[$index+1], $s[$index]]; @endphp
                                    @foreach($s as $id)<input type="hidden" name="ids[]" value="{{ $id }}">@endforeach
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center bg-gray-50 rounded-lg hover:bg-gray-100 transition text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-center">
                            <div class="flex justify-center gap-1">
                                <a href="{{ route('bank-accounts.edit', $account) }}" class="px-2.5 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">編集</a>
                                <form method="POST" action="{{ route('bank-accounts.destroy', $account) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-xs font-medium bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" onclick="return confirm('この口座を削除しますか？')">削除</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-sm text-gray-400 text-center">口座が登録されていません</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
