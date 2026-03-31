@extends('layouts.app')
@section('page-title', '口座登録')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">口座登録</h2>
        <p class="text-sm text-gray-500 mt-1">新しい口座を登録します</p>
    </div>

    <form method="POST" action="{{ route('bank-accounts.store') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">口座名</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="例：メイン口座、貯蓄用">
        </div>

        <div>
            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">銀行名</label>
            <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="例：三菱UFJ銀行">
        </div>

        <div>
            <label for="balance" class="block text-sm font-medium text-gray-700 mb-1">現在の残高</label>
            <input type="number" name="balance" id="balance" value="{{ old('balance', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="0">
            <p class="mt-1 text-xs text-gray-400">現時点の口座残高を入力してください</p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('bank-accounts.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">キャンセル</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">登録する</button>
        </div>
    </form>
</div>
@endsection
