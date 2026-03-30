@props(['status'])

@php
    $config = [
        'scheduled'    => ['label' => '予定', 'class' => 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'completed'    => ['label' => '完了', 'class' => 'bg-green-50 text-green-700 ring-green-600/20'],
        'carried_over' => ['label' => '繰越', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'cancelled'    => ['label' => '取消', 'class' => 'bg-gray-50 text-gray-600 ring-gray-500/10'],
    ];
    $item = $config[$status] ?? ['label' => $status, 'class' => 'bg-gray-50 text-gray-600 ring-gray-500/10'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 ring-inset {{ $item['class'] }}">
    {{ $item['label'] }}
</span>
