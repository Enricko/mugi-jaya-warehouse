<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index', $this->datasets());
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'stock');
        $data = $this->datasets();

        [$filename, $headers, $rows] = match ($type) {
            'consumption' => ['konsumsi-material', ['Material', 'SKU', 'Total Qty', 'Nilai'],
                $data['consumption']->map(fn ($r) => [$r['name'], $r['sku'], $r['qty'], $r['value']])],
            'driver' => ['riwayat-driver', ['Driver', 'Total', 'Delivered', 'Problem'],
                $data['drivers']->map(fn ($r) => [$r['name'], $r['total'], $r['delivered'], $r['problem']])],
            'valuation' => ['nilai-inventory', ['Gudang', 'Item', 'Nilai'],
                $data['valuation']->map(fn ($r) => [$r['name'], $r['items'], $r['value']])],
            default => ['stok-gudang', ['Gudang', 'Item', 'Total Qty', 'Nilai'],
                $data['stock']->map(fn ($r) => [$r['name'], $r['items'], $r['qty'], $r['value']])],
        };

        return Response::streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, "laporan-{$filename}-" . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function datasets(): array
    {
        $stocks = WarehouseStock::with(['material', 'warehouse'])->get();

        $stock = Warehouse::orderBy('name')->get()->map(function ($w) use ($stocks) {
            $rows = $stocks->where('warehouse_id', $w->id);

            return [
                'name' => $w->name,
                'items' => $rows->count(),
                'qty' => (float) $rows->sum('quantity'),
                'value' => $rows->sum(fn ($s) => (float) $s->quantity * (float) ($s->material->purchase_price ?? 0)),
            ];
        });

        $consumption = Transaction::with('material')->where('type', 'consumption')->get()
            ->groupBy('material_id')->map(function ($g) {
                $m = $g->first()->material;

                return ['name' => $m?->name ?? '—', 'sku' => $m?->sku ?? '—', 'qty' => (float) $g->sum('quantity'), 'value' => (float) $g->sum('amount')];
            })->values();

        $drivers = User::where('role', 'driver')->get()->map(function ($d) {
            $ships = Shipment::where('driver_id', $d->id)->get();

            return ['name' => $d->full_name, 'total' => $ships->count(),
                'delivered' => $ships->where('status', 'delivered')->count(),
                'problem' => $ships->where('status', 'problem')->count()];
        });

        $valuation = $stock; // same per-warehouse valuation basis

        return [
            'stock' => $stock,
            'consumption' => $consumption,
            'drivers' => $drivers,
            'valuation' => $valuation,
            'grandTotal' => $stock->sum('value'),
        ];
    }
}
