<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::with(['mandor', 'stocks.material'])->get()->map(function ($w) {
            $w->setAttribute('stock_value', $w->stocks->sum(fn ($s) => (float) $s->quantity * (float) ($s->material->purchase_price ?? 0)));
            $w->setAttribute('item_count', $w->stocks->count());
            $w->setAttribute('critical_count', $w->stocks->filter->isLow()->count());

            return $w;
        });

        return view('warehouses.index', compact('warehouses'));
    }

    public function show(Warehouse $warehouse): View
    {
        $warehouse->load('mandor');
        $stocks = $warehouse->stocks()->with(['material', 'project'])->get()->sortBy('material.name')->values();

        return view('warehouses.show', compact('warehouse', 'stocks'));
    }
}
