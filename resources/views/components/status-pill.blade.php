@props(['status' => ''])

@php
    $map = [
        // generic / transactions
        'pending'    => ['Pending', 'bg-amber-100 text-amber-700'],
        'approved'   => ['Approved', 'bg-green-100 text-green-700'],
        'rejected'   => ['Rejected', 'bg-red-100 text-red-700'],
        'completed'  => ['Completed', 'bg-slate-100 text-slate-600'],
        'draft'      => ['Draft', 'bg-slate-100 text-slate-500'],
        // purchase orders
        'ordered'    => ['Ordered', 'bg-blue-100 text-blue-700'],
        'received'   => ['Received', 'bg-green-100 text-green-700'],
        // shipments
        'confirmed'  => ['Confirmed', 'bg-amber-100 text-amber-700'],
        'in_transit' => ['In Transit', 'bg-blue-100 text-blue-700'],
        'delivered'  => ['Delivered', 'bg-green-100 text-green-700'],
        'problem'    => ['Problem', 'bg-red-100 text-red-700'],
        // projects
        'planning'   => ['Planning', 'bg-slate-100 text-slate-500'],
        'active'     => ['Active', 'bg-green-100 text-green-700'],
        'on_hold'    => ['On Hold', 'bg-amber-100 text-amber-700'],
    ];
    [$label, $classes] = $map[$status] ?? [ucfirst(str_replace('_', ' ', $status)), 'bg-slate-100 text-slate-600'];
@endphp

<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wide ring-1 ring-inset ring-black/5 whitespace-nowrap {{ $classes }}">
    {{ $label }}
</span>
