{{-- ロゴ --}}
<div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <span class="text-lg font-bold text-gray-800">Pay<span class="text-blue-600">Track</span></span>
    </a>
    <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded-lg text-gray-400 hover:bg-gray-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

{{-- ナビゲーション --}}
<nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
    @php
        $navItems = [
            ['route' => 'dashboard', 'pattern' => 'dashboard', 'label' => 'ダッシュボード', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'schedule', 'pattern' => 'schedule*', 'label' => '収支スケジュール', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['route' => 'transactions.index', 'pattern' => 'transactions.*', 'label' => '取引一覧', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['route' => 'fund-transfers.index', 'pattern' => 'fund-transfers.*', 'label' => '口座振替', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
            ['route' => 'rules.index', 'pattern' => 'rules.*', 'label' => 'ルール設定', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['route' => 'bank-accounts.index', 'pattern' => 'bank-accounts.*', 'label' => '口座管理', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ];
    @endphp

    @foreach($navItems as $item)
        @php $isActive = request()->routeIs($item['pattern']); @endphp
        <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
           class="flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition
                  {{ $isActive ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <svg class="w-5 h-5 mr-3 {{ $isActive ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/>
            </svg>
            {{ $item['label'] }}
        </a>
    @endforeach
</nav>

{{-- 使い方・アカウント・ログアウト --}}
<div class="px-4 py-4 border-t border-gray-100 space-y-1">
    <a href="{{ route('guide') }}" @click="sidebarOpen = false"
       class="flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-xl transition
              {{ request()->routeIs('guide') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('guide') ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        使い方
    </a>
    <a href="{{ route('account.edit') }}" @click="sidebarOpen = false"
       class="flex items-center w-full px-3 py-2.5 text-sm font-medium rounded-xl transition
              {{ request()->routeIs('account.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' }}">
        <svg class="w-5 h-5 mr-3 {{ request()->routeIs('account.*') ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        アカウント情報
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex items-center w-full px-3 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-xl transition">
            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            ログアウト
        </button>
    </form>
</div>
