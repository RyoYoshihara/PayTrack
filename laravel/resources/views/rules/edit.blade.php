@extends('layouts.app')
@section('page-title', 'ルール編集')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">ルール編集</h2>
        <p class="text-sm text-gray-500 mt-1">収支ルールの内容を変更します</p>
    </div>

    <form method="POST" action="{{ route('rules.update', $rule) }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5" x-data="{ recurrence: '{{ old('recurrence', $rule->recurrence) }}' }">
        @csrf @method('PUT')

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">タイトル</label>
            <input type="text" name="title" id="title" value="{{ old('title', $rule->title) }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">金額</label>
                <input type="number" name="amount" id="amount" value="{{ old('amount', $rule->amount) }}" min="1" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">タイプ</label>
                <select name="type" id="type" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="income" {{ old('type', $rule->type) === 'income' ? 'selected' : '' }}>収入</option>
                    <option value="expense" {{ old('type', $rule->type) === 'expense' ? 'selected' : '' }}>支出</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="recurrence" class="block text-sm font-medium text-gray-700 mb-1">繰返し</label>
                <select name="recurrence" id="recurrence" required x-model="recurrence"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="monthly">毎月</option>
                    <option value="once">一回のみ</option>
                </select>
            </div>
            <div x-show="recurrence === 'monthly'" x-transition>
                <label for="day_of_month" class="block text-sm font-medium text-gray-700 mb-1">
                    毎月の日付
                    <span class="relative inline-block ml-1" x-data="{ show: false }">
                        <button type="button" @mouseenter="show = true" @mouseleave="show = false" @focus="show = true" @blur="show = false"
                                class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-500 text-[10px] font-bold leading-none cursor-help hover:bg-blue-100 hover:text-blue-600 transition">?</button>
                        <div x-show="show" x-transition.opacity
                             class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg shadow-lg z-50 pointer-events-none">
                            <div class="font-semibold mb-1">月末に設定したい場合</div>
                            <div class="text-gray-300">「31」を指定すると、月末日として扱われます。例えば2月は28日（うるう年は29日）、4月は30日に自動調整されます。</div>
                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-px border-4 border-transparent border-t-gray-800"></div>
                        </div>
                    </span>
                </label>
                <input type="number" name="day_of_month" id="day_of_month" value="{{ old('day_of_month', $rule->day_of_month) }}" min="1" max="31"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="例：25（月末は31）">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="start_month" class="block text-sm font-medium text-gray-700 mb-1">開始月 <span class="text-gray-400 font-normal">（任意）</span></label>
                <input type="month" name="start_month" id="start_month" value="{{ old('start_month', $rule->start_month?->format('Y-m')) }}"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            </div>
            <div>
                <label for="end_month" class="block text-sm font-medium text-gray-700 mb-1">
                    終了月 <span class="text-gray-400 font-normal">（任意）</span>
                    <span class="relative inline-block ml-1" x-data="{ show: false }">
                        <button type="button" @mouseenter="show = true" @mouseleave="show = false" @focus="show = true" @blur="show = false"
                                class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 text-gray-500 text-[10px] font-bold leading-none cursor-help hover:bg-blue-100 hover:text-blue-600 transition">?</button>
                        <div x-show="show" x-transition.opacity
                             class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 px-3 py-2 bg-gray-800 text-white text-xs rounded-lg shadow-lg z-50 pointer-events-none">
                            未入力の場合、終了期限なし（無期限）として毎月自動生成され続けます。
                            <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-px border-4 border-transparent border-t-gray-800"></div>
                        </div>
                    </span>
                </label>
                <input type="month" name="end_month" id="end_month" value="{{ old('end_month', $rule->end_month?->format('Y-m')) }}"
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="未入力で無期限">
            </div>
        </div>

        <div>
            <label for="bank_account_id" class="block text-sm font-medium text-gray-700 mb-1">口座</label>
            <select name="bank_account_id" id="bank_account_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <option value="">選択してください</option>
                @foreach($bankAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('bank_account_id', $rule->bank_account_id) == $account->id ? 'selected' : '' }}>{{ $account->display_name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="memo" class="block text-sm font-medium text-gray-700 mb-1">メモ <span class="text-gray-400 font-normal">（任意）</span></label>
            <textarea name="memo" id="memo" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">{{ old('memo', $rule->memo) }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('rules.index') }}" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">キャンセル</a>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">更新する</button>
        </div>
    </form>
</div>
@endsection
