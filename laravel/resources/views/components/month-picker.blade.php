@props(['year', 'month', 'route'])

@php
    $prevMonth = $month - 1;
    $prevYear = $year;
    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
    $nextMonth = $month + 1;
    $nextYear = $year;
    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
@endphp

<div class="inline-flex items-center bg-white border border-gray-200 rounded-xl overflow-hidden">
    <a href="{{ route($route, array_merge(request()->except(['year', 'month']), ['year' => $prevYear, 'month' => $prevMonth])) }}"
       class="px-3 py-2 hover:bg-gray-50 text-gray-500 hover:text-gray-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <span class="px-4 py-2 text-sm font-semibold text-gray-800 border-x border-gray-200">{{ $year }}年{{ $month }}月</span>
    <a href="{{ route($route, array_merge(request()->except(['year', 'month']), ['year' => $nextYear, 'month' => $nextMonth])) }}"
       class="px-3 py-2 hover:bg-gray-50 text-gray-500 hover:text-gray-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>
