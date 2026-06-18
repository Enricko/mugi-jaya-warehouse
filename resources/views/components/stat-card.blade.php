@props([
    'label' => '',
    'value' => '',
    'trend' => null,        // e.g. "+3.2% vs minggu lalu"
    'trendUp' => true,
    'accent' => 'slate',    // slate | amber | green | red | blue
])

@php
    $valueColors = [
        'slate' => 'text-slate-800',
        'amber' => 'text-amber-600',
        'green' => 'text-green-600',
        'red'   => 'text-red-600',
        'blue'  => 'text-blue-600',
    ];
    $accentBars = [
        'slate' => 'border-l-slate-300',
        'amber' => 'border-l-amber-500',
        'green' => 'border-l-green-500',
        'red'   => 'border-l-red-500',
        'blue'  => 'border-l-blue-500',
    ];
    $valueColor = $valueColors[$accent] ?? $valueColors['slate'];
    $accentBar = $accentBars[$accent] ?? $accentBars['slate'];
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-xl border border-slate-200/70 border-l-4 {$accentBar} shadow-sm p-4 transition duration-200 hover:shadow-md hover:-translate-y-0.5"]) }}>
    <p class="text-[11px] font-semibold tracking-wide text-slate-400 uppercase">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold {{ $valueColor }}">{{ $value }}</p>
    @if($trend)
        <p class="mt-1 text-xs {{ $trendUp ? 'text-green-600' : 'text-red-500' }}">
            {{ $trendUp ? '↑' : '↓' }} {{ $trend }}
        </p>
    @endif
    {{ $slot }}
</div>
