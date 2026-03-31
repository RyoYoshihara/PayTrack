<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PayTrack') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- オーバーレイ（モバイル） --}}
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/30 z-40 lg:hidden" style="display: none;"></div>

        {{-- サイドバー: モバイル（スライドイン） --}}
        <aside x-show="sidebarOpen"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-200 flex flex-col z-50 lg:hidden"
               style="display: none;">
            @include('partials.sidebar-content')
        </aside>

        {{-- サイドバー: PC（常時表示） --}}
        <aside class="hidden lg:flex w-64 bg-white border-r border-gray-200 flex-col min-h-screen">
            @include('partials.sidebar-content')
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            {{-- トップバー --}}
            <header class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-2 rounded-lg text-gray-500 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'PayTrack')</h1>
                </div>
                <div class="hidden sm:flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ auth()->user()->email }}</span>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-4 sm:mb-6 rounded-xl bg-green-50 border border-green-200 p-3 sm:p-4 flex items-start space-x-3">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 sm:mb-6 rounded-xl bg-red-50 border border-red-200 p-3 sm:p-4 flex items-start space-x-3">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 sm:mb-6 rounded-xl bg-red-50 border border-red-200 p-3 sm:p-4">
                        <ul class="text-sm text-red-800 list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
