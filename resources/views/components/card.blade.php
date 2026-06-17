@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/70 shadow-sm']) }}>
    @if($title || isset($actions))
        <div class="flex items-start justify-between gap-3 px-5 pt-4 pb-3">
            <div>
                @if($title)<h3 class="font-semibold text-slate-800">{{ $title }}</h3>@endif
                @if($subtitle)<p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="shrink-0">{{ $actions }}</div>@endisset
        </div>
    @endif
    <div class="{{ ($title || isset($actions)) ? 'px-5 pb-5' : 'p-5' }}">
        {{ $slot }}
    </div>
</div>
