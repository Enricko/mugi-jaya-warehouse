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
    public function index(Request $request): View
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'warehouse' => $request->get('warehouse'),
        ];

        return view('reports.index', array_merge($this->datasets($filters), [
            'warehouses' => Warehouse::orderBy('name')->get(),
            'filters' => $filters,
        ]));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'stock');
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'warehouse' => $request->get('warehouse'),
        ];
        $data = $this->datasets($filters);

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

    private function datasets(array $filters = []): array
    {
        $stocksQuery = WarehouseStock::with(['material', 'warehouse']);
        if (! empty($filters['warehouse'])) {
            $stocksQuery->where('warehouse_id', $filters['warehouse']);
        }
        $stocks = $stocksQuery->get();

        $warehouseFilter = ! empty($filters['warehouse']) ? $filters['warehouse'] : null;

        $stock = Warehouse::orderBy('name')
            ->when($warehouseFilter, fn ($q) => $q->where('id', $warehouseFilter))
            ->get()
            ->map(function ($w) use ($stocks) {
                $rows = $stocks->where('warehouse_id', $w->id);

                return [
                    'name' => $w->name,
                    'items' => $rows->count(),
                    'qty' => (float) $rows->sum('quantity'),
                    'value' => $rows->sum(fn ($s) => (float) $s->quantity * (float) ($s->material->purchase_price ?? 0)),
                ];
            });

        $consumptionQuery = Transaction::with('material')->where('type', 'consumption');
        if (! empty($filters['date_from'])) {
            $consumptionQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $consumptionQuery->whereDate('created_at', '<=', $filters['date_to']);
        }
        $consumption = $consumptionQuery->get()
            ->groupBy('material_id')->map(function ($g) {
                $m = $g->first()->material;

                return ['name' => $m?->name ?? '—', 'sku' => $m?->sku ?? '—', 'qty' => (float) $g->sum('quantity'), 'value' => (float) $g->sum('amount')];
            })->values();

        $shipmentQuery = Shipment::query();
        if (! empty($filters['date_from'])) {
            $shipmentQuery->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $shipmentQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        $drivers = User::where('role', 'driver')->get()->map(function ($d) use ($shipmentQuery) {
            $ships = (clone $shipmentQuery)->where('driver_id', $d->id)->get();

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
