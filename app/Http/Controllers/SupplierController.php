<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::withCount('purchaseOrders');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%");
            });
        }
        if (($active = $request->get('active')) !== null && $active !== '') {
            $query->where('is_active', $active);
        }
        if (($external = $request->get('external')) !== null && $external !== '') {
            $query->where('is_external_island', $external);
        }

        return view('suppliers.index', [
            'suppliers' => $query->orderBy('name')->get(),
        ]);
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
