@extends('layouts.app')
@section('page-title', '使い方')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">使い方ガイド</h2>
        <p class="text-sm text-gray-500 mt-1">PayTrackの基本的な操作方法をご紹介します</p>
    </div>

    {{-- はじめに --}}
    <div class="bg-blue-50 rounded-2xl border border-blue-100 p-5 sm:p-6">
        <h3 class="text-base font-semibold text-blue-800 mb-2">PayTrackとは？</h3>
        <p class="text-sm text-blue-700 leading-relaxed">
            毎月の収入・支出の予定と実績を管理するシステムです。<br>
            ルール（テンプレート）を登録しておくと、毎月の取引データを自動生成できます。
        </p>
    </div>

    {{-- ステップ --}}
    @php
        $steps = [
            [
                'number' => '1',
                'title' => '口座を登録する',
                'description' => 'まず「口座管理」から、管理したい銀行口座を登録します。口座名（メイン口座など）と銀行名を入力してください。',
                'link' => route('bank-accounts.create'),
                'linkText' => '口座を登録する',
            ],
            [
                'number' => '2',
                'title' => 'ルールを登録する',
                'description' => '「ルール設定」から、毎月発生する収入・支出のルール（テンプレート）を登録します。例えば「給与：毎月25日・収入・300,000円」のように設定すると、毎月のデータ生成時に自動で取引が作られます。',
                'link' => route('rules.create'),
                'linkText' => 'ルールを登録する',
            ],
            [
                'number' => '3',
                'title' => '月次データを生成する',
                'description' => '「ダッシュボード」の一括操作にある「今月のデータ生成」ボタンを押すと、登録したルールをもとに今月分の取引が自動生成されます。すでに生成済みの場合はスキップされるので、何度押しても重複しません。',
                'link' => route('dashboard'),
                'linkText' => 'ダッシュボードを開く',
            ],
            [
                'number' => '4',
                'title' => '取引を管理する',
                'description' => '「収支スケジュール」または「取引一覧」から、各取引のステータスを管理します。実際に入金・支払が完了したら「完了」ボタンを押してください。臨時の取引は「取引一覧」から手動で登録することもできます。',
                'link' => route('schedule'),
                'linkText' => 'スケジュールを確認',
            ],
            [
                'number' => '5',
                'title' => '月末に繰り越し処理を行う',
                'description' => '月末時点で未処理（予定のまま）の取引がある場合は、ダッシュボードの「繰り越し処理」ボタンを押すと、翌月に自動で繰り越されます。',
                'link' => null,
                'linkText' => null,
            ],
        ];
    @endphp

    <div class="space-y-4">
        @foreach($steps as $step)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
            <div class="flex items-start space-x-4">
                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-white text-sm font-bold">{{ $step['number'] }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-800 mb-1">{{ $step['title'] }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $step['description'] }}</p>
                    @if($step['link'])
                    <a href="{{ $step['link'] }}" class="inline-flex items-center mt-3 text-sm font-medium text-blue-600 hover:text-blue-700 transition">
                        {{ $step['linkText'] }}
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 補足情報 --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <h3 class="text-base font-semibold text-gray-800 mb-4">その他の機能</h3>
        <div class="space-y-3">
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800">口座振替</p>
                    <p class="text-sm text-gray-500">口座間の資金移動を記録できます。振出元・振込先の双方を確認すると自動で完了になります。</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800">ダッシュボード</p>
                    <p class="text-sm text-gray-500">月ごとの収支合計や口座別のサマリーを確認できます。月ピッカーで過去の月も参照可能です。</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="w-6 h-6 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800">アカウント情報</p>
                    <p class="text-sm text-gray-500">メールアドレスやパスワードの変更が可能です。</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
