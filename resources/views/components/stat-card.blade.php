@props([
    'label' => '',
    'value' => '',
    'trend' => null,        // e.g. "+3.2% vs minggu lalu"
    'trendUp' => true,
    'accent' => 'slate',    // slate | amber | green | red | blue
])

@php
    $accents = [
        'slate' => 'text-slate-800',
        'amber' => 'text-amber-600',
        'green' => 'text-green-600',
        'red'   => 'text-red-600',
        'blue'  => 'text-blue-600',
    ];
    $valueColor = $accents[$accent] ?? $accents['slate'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/70 shadow-sm p-4']) }}>
    <p class="text-[11px] font-semibold tracking-wide text-slate-400 uppercase">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold {{ $valueColor }}">{{ $value }}</p>
    @if($trend)
        <p class="mt-1 text-xs {{ $trendUp ? 'text-green-600' : 'text-red-500' }}">
            {{ $trendUp ? '↑' : '↓' }} {{ $trend }}
        </p>
    @endif
    {{ $slot }}
</div>
