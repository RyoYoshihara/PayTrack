@extends('layouts.app')
@section('page-title', 'アカウント情報')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">アカウント情報</h2>
        <p class="text-sm text-gray-500 mt-1">メールアドレスやパスワードを変更できます</p>
    </div>

    {{-- メールアドレス変更 --}}
    <form method="POST" action="{{ route('account.update-email') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf @method('PATCH')

        <h3 class="text-base font-semibold text-gray-800">メールアドレスの変更</h3>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">メールアドレスを更新</button>
        </div>
    </form>

    {{-- パスワード変更 --}}
    <form method="POST" action="{{ route('account.update-password') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf @method('PATCH')

        <h3 class="text-base font-semibold text-gray-800">パスワードの変更</h3>

        <div>
            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">現在のパスワード</label>
            <input type="password" name="current_password" id="current_password" required autocomplete="current-password"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード</label>
            <input type="password" name="password" id="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            <p class="mt-1 text-xs text-gray-400">8文字以上、英字と数字を含めてください</p>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">新しいパスワード（確認）</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm">パスワードを更新</button>
        </div>
    </form>
</div>
@endsection
