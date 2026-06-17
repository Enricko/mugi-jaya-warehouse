<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GpsController extends Controller
{
    public function index(): View
    {
        $shipments = Shipment::with(['driver', 'project'])
            ->whereIn('status', ['confirmed', 'in_transit', 'problem', 'delivered'])
            ->latest()->get();

        $markers = $shipments->filter(fn ($s) => $s->last_gps_lat && $s->last_gps_lng)
            ->map(fn ($s) => [
                'code' => $s->code,
                'plate' => $s->vehicle_plate,
                'driver' => $s->driver->full_name,
                'project' => $s->project->name,
                'status' => $s->status,
                'lat' => (float) $s->last_gps_lat,
                'lng' => (float) $s->last_gps_lng,
            ])->values();

        return view('gps.index', [
            'shipments' => $shipments,
            'markers' => $markers,
            'counts' => $shipments->groupBy('status')->map->count(),
        ]);
    }

    /** Simulate GPS movement for in-transit shipments (no real mobile feed). */
    public function ping(): RedirectResponse
    {
        foreach (Shipment::where('status', 'in_transit')->get() as $s) {
            $s->update([
                'last_gps_lat' => (float) $s->last_gps_lat + (mt_rand(-30, 30) / 10000),
                'last_gps_lng' => (float) $s->last_gps_lng + (mt_rand(-30, 30) / 10000),
            ]);
        }

        return back()->with('success', 'Posisi GPS kendaraan diperbarui.');
    }
}
