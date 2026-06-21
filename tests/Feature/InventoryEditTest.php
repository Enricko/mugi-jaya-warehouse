<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryEditTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: User, 1: Warehouse, 2: Material, 3: WarehouseStock} */
    private function scaffold(float $qty): array
    {
        $boss = User::create([
            'email' => 'boss@test.com', 'password_hash' => bcrypt('x'),
            'full_name' => 'Boss', 'phone' => '0800', 'role' => 'kepala_gudang', 'is_active' => true,
        ]);
        $wh = Warehouse::create(['name' => 'Gudang Test', 'address' => 'Jl. Test', 'is_active' => true]);
        $material = Material::create([
            'sku' => 'TST-001', 'name' => 'Test Material', 'unit' => 'pcs',
            'purchase_price' => 1000, 'min_stock' => 5, 'category' => 'Aksesori',
        ]);
        $stock = WarehouseStock::create([
            'warehouse_id' => $wh->id, 'material_id' => $material->id,
            'project_id' => null, 'quantity' => $qty,
        ]);

        return [$boss, $wh, $material, $stock];
    }

    public function test_restock_adds_quantity(): void
    {
        [$boss, , , $stock] = $this->scaffold(10);

        $this->actingAs($boss)->post(route('inventory.restock', $stock), ['quantity' => 5])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertEquals(15.0, (float) $stock->fresh()->quantity);
    }

    public function test_adjust_sets_exact_quantity_and_records_reason(): void
    {
        [$boss, , , $stock] = $this->scaffold(10);

        $this->actingAs($boss)->post(route('inventory.adjust', $stock), [
            'quantity' => 7, 'reason' => 'selisih stok opname',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertEquals(7.0, (float) $stock->fresh()->quantity);
        $this->assertDatabaseHas('notifications', ['title' => 'Koreksi Stok', 'module' => 'Warehouse']);
    }

    public function test_adjust_requires_a_reason(): void
    {
        [$boss, , , $stock] = $this->scaffold(10);

        $this->actingAs($boss)->post(route('inventory.adjust', $stock), ['quantity' => 7])
            ->assertSessionHasErrors('reason');

        $this->assertEquals(10.0, (float) $stock->fresh()->quantity);
    }

    public function test_update_material_changes_fields(): void
    {
        [$boss, , $material] = $this->scaffold(10);

        $this->actingAs($boss)->patch(route('inventory.material.update', $material), [
            'sku' => $material->sku, 'name' => 'Nama Baru', 'unit' => 'pcs',
            'category' => 'Kaca', 'purchase_price' => 2500, 'min_stock' => 9,
        ])->assertRedirect()->assertSessionHas('success');

        $material->refresh();
        $this->assertSame('Nama Baru', $material->name);
        $this->assertSame('Kaca', $material->category);
        $this->assertEquals(2500.0, (float) $material->purchase_price);
        $this->assertEquals(9, $material->min_stock);
    }

    public function test_destroy_stock_removes_only_that_row(): void
    {
        [$boss, , $material, $stock] = $this->scaffold(10);

        $this->actingAs($boss)->delete(route('inventory.destroy', $stock))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('warehouse_stock', ['id' => $stock->id]);
        $this->assertDatabaseHas('materials', ['id' => $material->id]); // catalogue intact
    }

    public function test_destroy_material_is_blocked_when_referenced(): void
    {
        [$boss, , $material, $stock] = $this->scaffold(10);
        Transaction::create([
            'code' => 'CON-9001', 'type' => 'consumption', 'status' => 'completed',
            'material_id' => $material->id, 'quantity' => 1, 'created_by' => $boss->id, 'amount' => 1,
        ]);

        $this->actingAs($boss)->delete(route('inventory.material.destroy', $material))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('materials', ['id' => $material->id]);
    }

    public function test_destroy_material_cascades_when_unreferenced(): void
    {
        [$boss, , $material, $stock] = $this->scaffold(10);

        $this->actingAs($boss)->delete(route('inventory.material.destroy', $material))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        $this->assertDatabaseMissing('warehouse_stock', ['id' => $stock->id]);
    }

    public function test_inventory_page_renders_with_actions(): void
    {
        [$boss, , $material] = $this->scaffold(10);

        $this->actingAs($boss)->get(route('inventory.index'))
            ->assertOk()
            ->assertSee($material->sku)
            ->assertSee('Koreksi');
    }
}
