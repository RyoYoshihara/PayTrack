@extends('layouts.app')
@section('page-title', '振替登録')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">振替登録</h2>
        <p class="text-sm text-gray-500 mt-1">口座間の振替を登録します</p>
    </div>

    <form method="POST" action="{{ route('fund-transfers.store') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="from_account_id" class="block text-sm font-medium text-gray-700 mb-1">振出元口座</label>
                <select name="from_account_id" id="from_account_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">選択してください</option>
                    @foreach($bankAccounts as $account)
                        <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }}>{{ $account->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="to_account_id" class="block text-sm font-medium text-gray-700 mb-1">振込先口座</label>
                <select name="to_account_id" id="to_account_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">選択してください</option>
                    @foreach($bankAccounts as $account)
                        <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>{{ $account->display_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">金額</label>
                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" min="1" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="0">
            </div>
            <div>
                <label for="scheduled_date" class="block text-sm font-medium text-gray-700 mb-1">予定日</label>
                <input type="date" name="scheduled_date" id="scheduled_date" value="{{ old('scheduled_date', now()->toDateString()) }}" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
        </div>

        <div>
            <label for="memo" class="block text-sm font-medium text-gray-700 mb-1">メモ <span class="text-gray-400 font-normal">（任意）</span></label>
            <textarea name="memo" id="memo" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="補足事項があれば入力">{{ old('memo') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('fund-transfers.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">キャンセル</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">登録する</button>
        </div>
    </form>
</div>
@endsection
