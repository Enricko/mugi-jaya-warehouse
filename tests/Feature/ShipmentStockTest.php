<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentStockTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{boss: User, driver: User, wh: Warehouse, material: Material, project: Project, stock: WarehouseStock} */
    private function scaffold(float $stockQty): array
    {
        $boss = User::create([
            'email' => 'boss@test.com', 'password_hash' => bcrypt('x'),
            'full_name' => 'Boss', 'phone' => '0800', 'role' => 'kepala_gudang', 'is_active' => true,
        ]);
        $driver = User::create([
            'email' => 'driver@test.com', 'password_hash' => bcrypt('x'),
            'full_name' => 'Driver', 'phone' => '0801', 'role' => 'driver', 'is_active' => true,
        ]);
        $wh = Warehouse::create(['name' => 'Gudang Test', 'address' => 'Jl. Test', 'is_active' => true]);
        $material = Material::create([
            'sku' => 'TST-001', 'name' => 'Test Material', 'unit' => 'pcs',
            'purchase_price' => 1000, 'min_stock' => 5, 'category' => 'Aksesori',
        ]);
        $project = Project::create([
            'name' => 'Proyek Test', 'client_name' => 'Client', 'location' => 'Loc', 'status' => 'active',
        ]);
        $stock = WarehouseStock::create([
            'warehouse_id' => $wh->id, 'material_id' => $material->id,
            'project_id' => null, 'quantity' => $stockQty,
        ]);

        return compact('boss', 'driver', 'wh', 'material', 'project', 'stock');
    }

    public function test_shipment_deducts_stock_and_records_consumption(): void
    {
        ['boss' => $boss, 'driver' => $driver, 'wh' => $wh, 'material' => $material, 'project' => $project, 'stock' => $stock] = $this->scaffold(40);

        $this->actingAs($boss)->post(route('shipments.store'), [
            'project_id' => $project->id,
            'warehouse_id' => $wh->id,
            'driver_id' => $driver->id,
            'vehicle_plate' => 'B 1234 XX',
            'items' => [['material_id' => $material->id, 'quantity' => 8]],
        ])->assertRedirect();

        // Stock dropped 40 → 32
        $this->assertEquals(32.0, (float) $stock->fresh()->quantity);

        // One consumption transaction recorded with the right value (8 × 1000)
        $tx = Transaction::where('type', 'consumption')->firstOrFail();
        $this->assertSame('completed', $tx->status);
        $this->assertEquals($material->id, $tx->material_id);
        $this->assertEquals($project->id, $tx->project_id);
        $this->assertEquals($wh->id, $tx->from_warehouse_id);
        $this->assertEquals(8.0, (float) $tx->quantity);
        $this->assertEquals(8000.0, (float) $tx->amount);
    }

    public function test_shipment_is_blocked_when_stock_insufficient(): void
    {
        ['boss' => $boss, 'driver' => $driver, 'wh' => $wh, 'material' => $material, 'project' => $project, 'stock' => $stock] = $this->scaffold(5);

        $this->actingAs($boss)->post(route('shipments.store'), [
            'project_id' => $project->id,
            'warehouse_id' => $wh->id,
            'driver_id' => $driver->id,
            'vehicle_plate' => 'B 1234 XX',
            'items' => [['material_id' => $material->id, 'quantity' => 9999]],
        ])->assertRedirect()->assertSessionHas('error');

        // Nothing changed: stock intact, no shipment, no transaction
        $this->assertEquals(5.0, (float) $stock->fresh()->quantity);
        $this->assertDatabaseCount('shipments', 0);
        $this->assertDatabaseCount('transactions', 0);
    }
}
