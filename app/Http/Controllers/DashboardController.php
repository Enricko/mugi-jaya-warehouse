<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Shipment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return match ($user->role) {
            'owner' => $this->owner($user),
            'kepala_gudang' => $this->operational($user),
            'driver' => $this->driver($user),
            'mandor' => $this->mandor($user),
            default => view('dashboard.index', ['user' => $user]),
        };
    }

    /** Owner — high level summary dashboard. */
    private function owner(User $user): View
    {
        $stocks = WarehouseStock::with('material')->get();
        $inventoryValue = $stocks->sum(fn ($s) => (float) $s->quantity * (float) ($s->material->purchase_price ?? 0));
        $criticalCount = $stocks->filter->isLow()->count();

        $pendingTransfers = Transaction::with(['fromWarehouse', 'toWarehouse', 'material', 'creator'])
            ->where('type', 'transfer')->where('status', 'pending')->latest()->get();
        $pendingPos = PurchaseOrder::with(['supplier', 'creator'])->where('status', 'pending')->latest()->get();

        $activeShipments = Shipment::with(['driver', 'project'])->where('status', 'in_transit')->get();
        $activeShipmentsJson = $activeShipments->map(fn ($s) => [
            'plate' => $s->vehicle_plate,
            'driver' => $s->driver->full_name,
            'lat' => (float) $s->last_gps_lat,
            'lng' => (float) $s->last_gps_lng,
            'project' => $s->project->name,
        ])->values();

        return view('dashboard.owner', [
            'user' => $user,
            'kpi' => [
                'inventory_value' => $inventoryValue,
                'active_projects' => Project::where('status', 'active')->count(),
                'shipments_today' => Shipment::whereDate('created_at', today())->count(),
                'in_transit' => Shipment::where('status', 'in_transit')->count(),
                'critical' => $criticalCount,
            ],
            'consumptionChart' => $this->dailySeries('consumption'),
            'shipmentChart' => $this->shipmentSeries(),
            'pendingTransfers' => $pendingTransfers,
            'pendingPos' => $pendingPos,
            'activeShipments' => $activeShipments,
            'activeShipmentsJson' => $activeShipmentsJson,
            'recentActivity' => Transaction::with(['creator', 'material'])->latest()->limit(6)->get(),
        ]);
    }

    /** Kepala Gudang — operational dashboard. */
    private function operational(User $user): View
    {
        $stocks = WarehouseStock::with(['material', 'warehouse'])->get();
        $critical = $stocks->filter->isLow()
            ->sortBy(fn ($s) => $s->quantity)
            ->values();
        $untagged = $stocks->whereNull('location_tag')->values();

        $pendingTransfers = Transaction::with(['fromWarehouse', 'toWarehouse', 'material', 'creator'])
            ->where('type', 'transfer')->where('status', 'pending')->latest()->get();

        $mandors = User::where('role', 'mandor')->with('managedWarehouse')->get();

        return view('dashboard.operational', [
            'user' => $user,
            'criticalCount' => $critical->count(),
            'critical' => $critical->take(4),
            'untagged' => $untagged,
            'pendingTransfers' => $pendingTransfers,
            'pendingPoCount' => PurchaseOrder::where('status', 'pending')->count(),
            'mandors' => $mandors,
        ]);
    }

    /** Driver — specific dashboard for active deliveries. */
    private function driver(User $user): View
    {
        $activeShipments = Shipment::with(['project', 'warehouse', 'items'])
            ->where('driver_id', $user->id)
            ->whereIn('status', ['confirmed', 'in_transit', 'problem'])
            ->latest()
            ->get();
            
        $todayDeliveriesCount = Shipment::where('driver_id', $user->id)
            ->whereDate('delivered_at', today())
            ->where('status', 'delivered')
            ->count();
            
        return view('dashboard.driver', [
            'user' => $user,
            'activeShipments' => $activeShipments,
            'todayDeliveriesCount' => $todayDeliveriesCount,
        ]);
    }
    
    /** Mandor — specific dashboard for managed warehouse. */
    private function mandor(User $user): View
    {
        $warehouse = clone $user->managedWarehouse;
        $inboundPending = collect();
        $inboundRecent = collect();
        $criticalCount = 0;
        
        if ($warehouse) {
            $inboundPending = Transaction::with(['material', 'creator'])
                ->where('to_warehouse_id', $warehouse->id)
                ->where('type', 'inbound')
                ->where('status', 'pending')
                ->latest()
                ->get();
                
            $inboundRecent = Transaction::with(['material', 'creator'])
                ->where('to_warehouse_id', $warehouse->id)
                ->where('type', 'inbound')
                ->whereIn('status', ['completed', 'approved'])
                ->latest()
                ->limit(5)
                ->get();
                
            $criticalCount = WarehouseStock::where('warehouse_id', $warehouse->id)->get()->filter->isLow()->count();
        }
        
        return view('dashboard.mandor', [
            'user' => $user,
            'warehouse' => $warehouse,
            'inboundPending' => $inboundPending,
            'inboundRecent' => $inboundRecent,
            'criticalCount' => $criticalCount,
        ]);
    }

    /** Daily total amount series for the last 14 days for a transaction type. */
    private function dailySeries(string $type): array
    {
        $rows = Transaction::where('type', $type)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get()
            ->groupBy(fn ($t) => $t->created_at->format('Y-m-d'));

        $labels = [];
        $data = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->format('d M');
            $data[] = (float) ($rows->get($day->format('Y-m-d'))?->sum('amount') ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /** Delivered vs problem shipment counts for the last 14 days. */
    private function shipmentSeries(): array
    {
        $rows = Shipment::where('created_at', '>=', now()->subDays(13)->startOfDay())->get()
            ->groupBy(fn ($s) => $s->created_at->format('Y-m-d'));

        $labels = [];
        $delivered = [];
        $problem = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->format('d M');
            $bucket = $rows->get($day->format('Y-m-d'));
            $delivered[] = $bucket ? $bucket->whereIn('status', ['delivered', 'in_transit', 'confirmed'])->count() : 0;
            $problem[] = $bucket ? $bucket->where('status', 'problem')->count() : 0;
        }

        return ['labels' => $labels, 'delivered' => $delivered, 'problem' => $problem];
    }
}
