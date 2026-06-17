<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Notification;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@mugijaya.com'],
            ['password_hash' => Hash::make('password'), 'full_name' => 'Pak Sukma', 'phone' => '0811000001', 'role' => 'owner', 'is_active' => true],
        );

        // ── Users ────────────────────────────────────────────────
        $yudi = $this->user('yudi@mugijaya.com', 'Pak Yudi', '0811000002', 'kepala_gudang', $owner->id);

        $andi = $this->user('andi@mugijaya.com', 'Mandor Andi', '0812000001', 'mandor', $yudi->id);
        $rian = $this->user('rian@mugijaya.com', 'Mandor Rian', '0812000002', 'mandor', $yudi->id);
        $eko  = $this->user('eko@mugijaya.com', 'Mandor Eko', '0812000003', 'mandor', $yudi->id);
        $joko = $this->user('joko@mugijaya.com', 'Mandor Joko', '0812000004', 'mandor', $yudi->id);

        $bayu  = $this->user('bayu@mugijaya.com', 'Bayu Pratama', '0813000001', 'driver', $yudi->id);
        $rianh = $this->user('rianh@mugijaya.com', 'Rian Hidayat', '0813000002', 'driver', $yudi->id);
        $jokos = $this->user('jokos@mugijaya.com', 'Joko Susilo', '0813000003', 'driver', $yudi->id);
        $ekow  = $this->user('ekow@mugijaya.com', 'Eko Wijaya', '0813000004', 'driver', $yudi->id);

        $this->user('deni@mugijaya.com', 'Deni Engineering', '0814000001', 'engineering', $yudi->id);

        // ── Warehouses ───────────────────────────────────────────
        $gA = Warehouse::create(['name' => 'Gudang A — Cikarang Utara', 'address' => 'Jl. Industri Raya No. 12, Cikarang Utara, Bekasi', 'mandor_id' => $andi->id, 'latitude' => -6.2789, 'longitude' => 107.1520, 'is_active' => true]);
        $gB = Warehouse::create(['name' => 'Gudang B — Bekasi Barat', 'address' => 'Jl. Sudirman No. 88, Bekasi Barat', 'mandor_id' => $rian->id, 'latitude' => -6.2349, 'longitude' => 106.9896, 'is_active' => true]);
        $gC = Warehouse::create(['name' => 'Gudang C — Cikarang Selatan', 'address' => 'Kawasan Jababeka II, Cikarang Selatan, Bekasi', 'mandor_id' => $eko->id, 'latitude' => -6.3410, 'longitude' => 107.1330, 'is_active' => true]);
        $gD = Warehouse::create(['name' => 'Gudang D — Tambun', 'address' => 'Jl. Diponegoro No. 5, Tambun Selatan, Bekasi', 'mandor_id' => $joko->id, 'latitude' => -6.2615, 'longitude' => 107.0530, 'is_active' => true]);

        // ── Materials ────────────────────────────────────────────
        $m = [];
        foreach ([
            ['ALM-004', 'Aluminium 4mm Profile', 'm', 175000, 50, 'Aluminium'],
            ['ALM-006', 'Aluminium 6mm Profile', 'm', 140000, 50, 'Aluminium'],
            ['ALM-008', 'Aluminium 8mm Profile', 'm', 180000, 30, 'Aluminium'],
            ['ALM-040', 'Aluminium 40x40 Tube', 'm', 150000, 25, 'Aluminium'],
            ['KCA-005', 'Kaca Bening 5mm', 'lembar', 200000, 50, 'Kaca'],
            ['KCA-008', 'Kaca Tempered 8mm', 'lembar', 800000, 25, 'Kaca'],
            ['KCA-010', 'Kaca Riben 10mm', 'lembar', 300000, 20, 'Kaca'],
            ['AKS-012', 'Handle Pintu Premium', 'pcs', 300000, 30, 'Aksesori'],
            ['AKS-E20', 'Engsel Pintu Geser', 'pcs', 25000, 50, 'Aksesori'],
            ['AKS-K22', 'Kunci Pintu Geser', 'pcs', 60000, 40, 'Aksesori'],
        ] as [$sku, $name, $unit, $price, $min, $cat]) {
            $m[$sku] = Material::create(['sku' => $sku, 'name' => $name, 'unit' => $unit, 'purchase_price' => $price, 'min_stock' => $min, 'category' => $cat]);
        }

        // ── Projects ─────────────────────────────────────────────
        $pCitra  = Project::create(['name' => 'Apartemen Citra Garden', 'client_name' => 'PT Citra Land', 'location' => 'Jl. Citra Garden 5, Jakarta Barat', 'status' => 'active', 'start_date' => '2026-03-01', 'end_date' => '2026-09-30']);
        $pSenayan= Project::create(['name' => 'Mall Senayan City', 'client_name' => 'PT Senayan Management', 'location' => 'Jl. Asia Afrika, Jakarta Pusat', 'status' => 'active', 'start_date' => '2026-02-15', 'end_date' => '2026-08-15']);
        $pPondok = Project::create(['name' => 'Hotel Pondok Indah', 'client_name' => 'PT Pondok Indah Group', 'location' => 'Jl. Metro Pondok Indah, Jakarta Selatan', 'status' => 'active', 'start_date' => '2026-04-01', 'end_date' => '2026-11-30']);
        $pOffice = Project::create(['name' => 'Office Tower 88', 'client_name' => 'PT Office Eighty Eight', 'location' => 'Jl. Casablanca, Jakarta Selatan', 'status' => 'active', 'start_date' => '2026-01-10', 'end_date' => '2026-07-20']);
        Project::create(['name' => 'Gedung Serbaguna Bekasi', 'client_name' => 'Pemkot Bekasi', 'location' => 'Alun-alun Bekasi', 'status' => 'planning', 'start_date' => null, 'end_date' => null]);

        // ── Suppliers ────────────────────────────────────────────
        $sAlumindo = Supplier::create(['name' => 'PT Sumber Alumindo', 'address' => 'Kawasan Industri MM2100, Cibitung', 'contact_phone' => '02188001122', 'city' => 'Bekasi', 'is_external_island' => false, 'is_active' => true]);
        $sMitra    = Supplier::create(['name' => 'CV Mitra Indo', 'address' => 'Jl. Mangga Dua Raya, Jakarta Utara', 'contact_phone' => '02160012233', 'city' => 'Jakarta', 'is_external_island' => false, 'is_active' => true]);
        Supplier::create(['name' => 'PT Kaca Sejahtera', 'address' => 'Jl. Rungkut Industri, Surabaya', 'contact_phone' => '03184005566', 'city' => 'Surabaya', 'is_external_island' => false, 'is_active' => true]);
        Supplier::create(['name' => 'UD Makassar Glass', 'address' => 'Jl. Perintis Kemerdekaan, Makassar', 'contact_phone' => '04113330011', 'city' => 'Makassar', 'is_external_island' => true, 'is_active' => true]);

        // ── Warehouse stock ──────────────────────────────────────
        // [warehouse, materialSku, project(nullable), qty, location_tag(nullable)]
        $stockRows = [
            [$gA, 'ALM-004', $pCitra, 320, 'Rak A3'],
            [$gA, 'KCA-005', null, 200, 'Rak A6'],        // source for TRF-0089
            [$gA, 'ALM-006', null, 8, 'Rak A4'],          // LOW (min 50)
            [$gA, 'ALM-008', null, 40, null],             // untagged location
            [$gA, 'AKS-012', $pCitra, 4, 'Rak A7'],       // LOW (min 30)
            [$gA, 'AKS-K22', null, 60, null],             // untagged
            [$gB, 'KCA-005', $pSenayan, 12, 'Blok B1'],   // LOW (min 50)
            [$gB, 'KCA-010', null, 24, null],             // untagged
            [$gB, 'ALM-040', $pOffice, 18, 'Rak B3'],     // LOW (min 25)
            [$gB, 'ALM-004', null, 540, 'Rak B2'],
            [$gC, 'KCA-008', $pPondok, 6, 'Blok C2'],     // LOW (min 25)
            [$gC, 'AKS-E20', null, 600, 'Blok C5'],
            [$gC, 'ALM-006', null, 410, 'Blok C1'],
            [$gD, 'KCA-005', null, 320, 'Rak D1'],
            [$gD, 'AKS-012', null, 150, 'Rak D2'],
            [$gD, 'ALM-008', null, 220, 'Rak D4'],
        ];
        foreach ($stockRows as [$wh, $sku, $proj, $qty, $loc]) {
            WarehouseStock::create([
                'warehouse_id' => $wh->id,
                'material_id' => $m[$sku]->id,
                'project_id' => $proj?->id,
                'quantity' => $qty,
                'location_tag' => $loc,
            ]);
        }

        // ── Purchase Orders ──────────────────────────────────────
        $po1 = PurchaseOrder::create(['po_number' => 'PO-2026-0149', 'supplier_id' => $sAlumindo->id, 'created_by' => $yudi->id, 'approved_by' => $owner->id, 'status' => 'ordered', 'total_estimated' => 184560000, 'needed_date' => '2026-06-25']);
        $po1->items()->createMany([
            ['material_id' => $m['ALM-006']->id, 'quantity' => 120, 'unit_price' => 140000, 'subtotal' => 16800000],
            ['material_id' => $m['ALM-004']->id, 'quantity' => 240, 'unit_price' => 175000, 'subtotal' => 42000000],
            ['material_id' => $m['KCA-005']->id, 'quantity' => 200, 'unit_price' => 200000, 'subtotal' => 40000000],
            ['material_id' => $m['KCA-008']->id, 'quantity' => 108, 'unit_price' => 800000, 'subtotal' => 86400000],
        ]);

        $po2 = PurchaseOrder::create(['po_number' => 'PO-2026-0142', 'supplier_id' => $sAlumindo->id, 'created_by' => $yudi->id, 'status' => 'pending', 'total_estimated' => 184560000, 'needed_date' => '2026-06-28']);
        $po2->items()->create(['material_id' => $m['ALM-004']->id, 'quantity' => 240, 'unit_price' => 769000, 'subtotal' => 184560000, 'notes' => 'Aluminium 4mm x 240 lembar']);

        $po3 = PurchaseOrder::create(['po_number' => 'PO-2026-0141', 'supplier_id' => $sMitra->id, 'created_by' => $yudi->id, 'status' => 'pending', 'total_estimated' => 12460000, 'needed_date' => '2026-06-30']);
        $po3->items()->create(['material_id' => $m['AKS-012']->id, 'quantity' => 41, 'unit_price' => 304000, 'subtotal' => 12460000, 'notes' => 'Aksesori handle pintu']);

        $po4 = PurchaseOrder::create(['po_number' => 'PO-2026-0150', 'supplier_id' => $sMitra->id, 'created_by' => $yudi->id, 'status' => 'draft', 'total_estimated' => 9000000, 'needed_date' => '2026-07-05']);
        $po4->items()->create(['material_id' => $m['KCA-010']->id, 'quantity' => 30, 'unit_price' => 300000, 'subtotal' => 9000000]);

        // ── Transactions (pending transfers for approval) ────────
        Transaction::create(['code' => 'TRF-0089', 'type' => 'transfer', 'status' => 'pending', 'from_warehouse_id' => $gA->id, 'to_warehouse_id' => $gC->id, 'material_id' => $m['KCA-005']->id, 'quantity' => 80, 'created_by' => $rian->id, 'amount' => 62300000, 'notes' => 'Kaca Bening 5mm - 80 lembar untuk Proyek Hotel Pondok Indah']);
        Transaction::create(['code' => 'TRF-0090', 'type' => 'transfer', 'status' => 'pending', 'from_warehouse_id' => $gB->id, 'to_warehouse_id' => $gA->id, 'material_id' => $m['ALM-004']->id, 'quantity' => 240, 'created_by' => $andi->id, 'amount' => 48100000, 'notes' => 'Aluminium 4mm Profile - 240 m']);
        Transaction::create(['code' => 'TRF-0091', 'type' => 'transfer', 'status' => 'pending', 'from_warehouse_id' => $gC->id, 'to_warehouse_id' => $gD->id, 'material_id' => $m['AKS-E20']->id, 'quantity' => 350, 'created_by' => $eko->id, 'amount' => 8750000, 'notes' => 'Engsel Pintu Geser - 350 pcs']);

        // Completed transactions for activity feed + reports
        Transaction::create(['code' => 'INB-0210', 'type' => 'inbound', 'status' => 'completed', 'reference_id' => $po1->id, 'to_warehouse_id' => $gA->id, 'material_id' => $m['ALM-006']->id, 'quantity' => 120, 'created_by' => $andi->id, 'amount' => 16800000, 'notes' => 'Penerimaan dari PO-2026-0149']);
        Transaction::create(['code' => 'CON-0305', 'type' => 'consumption', 'status' => 'completed', 'from_warehouse_id' => $gA->id, 'project_id' => $pCitra->id, 'material_id' => $m['ALM-004']->id, 'quantity' => 40, 'created_by' => $andi->id, 'amount' => 7000000, 'notes' => 'Pemakaian material di Apartemen Citra Garden']);
        Transaction::create(['code' => 'TRF-0088', 'type' => 'transfer', 'status' => 'approved', 'from_warehouse_id' => $gB->id, 'to_warehouse_id' => $gC->id, 'material_id' => $m['ALM-006']->id, 'quantity' => 60, 'created_by' => $rian->id, 'approved_by' => $yudi->id, 'amount' => 8400000, 'notes' => 'Transfer disetujui minggu lalu']);

        // ── Shipments ────────────────────────────────────────────
        $sh1 = Shipment::create(['code' => 'SHP-0894', 'project_id' => $pCitra->id, 'warehouse_id' => $gA->id, 'driver_id' => $bayu->id, 'vehicle_plate' => 'B 9143 TKO', 'status' => 'in_transit', 'last_gps_lat' => -6.2200, 'last_gps_lng' => 106.9000, 'created_at' => Carbon::now()->subHours(2)]);
        $sh1->items()->createMany([
            ['material_id' => $m['ALM-004']->id, 'quantity' => 8],
            ['material_id' => $m['KCA-005']->id, 'quantity' => 4],
        ]);

        $sh2 = Shipment::create(['code' => 'SHP-0895', 'project_id' => $pSenayan->id, 'warehouse_id' => $gB->id, 'driver_id' => $rianh->id, 'vehicle_plate' => 'B 9028 PVR', 'status' => 'in_transit', 'last_gps_lat' => -6.2280, 'last_gps_lng' => 106.8100, 'created_at' => Carbon::now()->subHours(3)]);
        $sh2->items()->create(['material_id' => $m['ALM-006']->id, 'quantity' => 10]);

        $sh3 = Shipment::create(['code' => 'SHP-0896', 'project_id' => $pOffice->id, 'warehouse_id' => $gC->id, 'driver_id' => $jokos->id, 'vehicle_plate' => 'B 7891 KCM', 'status' => 'delivered', 'receiver_name' => 'Hendrawan Setiadi', 'last_gps_lat' => -6.2240, 'last_gps_lng' => 106.8420, 'delivered_at' => Carbon::now()->subHour(), 'created_at' => Carbon::now()->subHours(5)]);
        $sh3->items()->create(['material_id' => $m['AKS-012']->id, 'quantity' => 20]);

        $sh4 = Shipment::create(['code' => 'SHP-0897', 'project_id' => $pPondok->id, 'warehouse_id' => $gD->id, 'driver_id' => $ekow->id, 'vehicle_plate' => 'B 6612 SLA', 'status' => 'problem', 'last_gps_lat' => -6.2900, 'last_gps_lng' => 106.7800, 'created_at' => Carbon::now()->subHours(4)]);
        $sh4->items()->create(['material_id' => $m['KCA-008']->id, 'quantity' => 6]);

        $sh5 = Shipment::create(['code' => 'SHP-0898', 'project_id' => $pSenayan->id, 'warehouse_id' => $gA->id, 'driver_id' => $bayu->id, 'vehicle_plate' => 'B 9143 TKO', 'status' => 'confirmed', 'created_at' => Carbon::now()->subMinutes(30)]);
        $sh5->items()->create(['material_id' => $m['ALM-008']->id, 'quantity' => 8]);

        // ── Notifications (Owner + Pak Yudi) ─────────────────────
        foreach ([$owner, $yudi] as $u) {
            Notification::create(['user_id' => $u->id, 'title' => 'Stok Kritis', 'message' => 'Kaca Bening 5mm di Gudang B mencapai batas minimum (12 dari 50).', 'type' => 'alert', 'module' => 'Warehouse', 'is_read' => false]);
            Notification::create(['user_id' => $u->id, 'title' => 'Transfer Menunggu Approval', 'message' => 'TRF-0089 Gudang A → Gudang C menunggu persetujuan Anda.', 'type' => 'warning', 'module' => 'Warehouse', 'is_read' => false]);
            Notification::create(['user_id' => $u->id, 'title' => 'Pengiriman Bermasalah', 'message' => 'SHP-0897 dilaporkan bermasalah oleh Driver Eko Wijaya.', 'type' => 'alert', 'module' => 'Shipment', 'is_read' => false]);
            Notification::create(['user_id' => $u->id, 'title' => 'Pengiriman Selesai', 'message' => 'SHP-0896 telah delivered di Office Tower 88.', 'type' => 'info', 'module' => 'Shipment', 'is_read' => true, 'read_at' => Carbon::now()->subHour()]);
        }

        $this->command->info('Demo data seeded. Login: owner@mugijaya.com / yudi@mugijaya.com — password: password');
    }

    private function user(string $email, string $name, string $phone, string $role, ?string $createdBy): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            ['password_hash' => Hash::make('password'), 'full_name' => $name, 'phone' => $phone, 'role' => $role, 'created_by' => $createdBy, 'is_active' => true],
        );
    }
}
