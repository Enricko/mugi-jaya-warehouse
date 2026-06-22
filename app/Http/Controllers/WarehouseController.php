<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $mandors = User::where('role', 'mandor')->orderBy('full_name')->get();

        return view('warehouses.index', compact('warehouses', 'mandors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'mandor_id' => 'nullable|exists:users,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $warehouse = Warehouse::create($data + ['is_active' => true]);

        return redirect()->route('warehouses.show', $warehouse)->with('success', "Gudang {$warehouse->name} berhasil dibuat.");
    }

    public function show(Warehouse $warehouse): View
    {
        $warehouse->load('mandor');
        $stocks = $warehouse->stocks()->with(['material', 'project'])->get()->sortBy('material.name')->values();

        return view('warehouses.show', compact('warehouse', 'stocks'));
    }
}
