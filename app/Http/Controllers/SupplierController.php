<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::withCount('purchaseOrders')->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'address' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'is_external_island' => 'nullable|boolean',
        ]);

        Supplier::create([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'city' => $data['city'] ?? null,
            'is_external_island' => $request->boolean('is_external_island'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function toggle(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['is_active' => ! $supplier->is_active]);

        return back()->with('success', 'Status supplier diperbarui.');
    }
}
