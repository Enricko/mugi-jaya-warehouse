<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GpsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Mugi Jaya Warehouse & Shipping Management
|--------------------------------------------------------------------------
*/

// ── Guest / Authentication ────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Authenticated ─────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Notifications (all roles) ──────────────────────────────────
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifikasi/{notification}/baca', [NotificationController::class, 'markRead'])->name('notifications.read');

    // ── Warehouse & Inventory (Owner + Kepala Gudang) ──────────────
    Route::middleware('role:owner,kepala_gudang')->group(function () {
        Route::get('/gudang', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/gudang', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/gudang/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');

        Route::get('/inventaris', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventaris/material', [InventoryController::class, 'store'])->name('inventory.store');
        Route::post('/inventaris/stok', [InventoryController::class, 'addStock'])->name('inventory.stock');
        Route::post('/inventaris/{stock}/tag', [InventoryController::class, 'tagLocation'])->name('inventory.tag');
        Route::post('/inventaris/{stock}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
        Route::post('/inventaris/{stock}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::delete('/inventaris/{stock}', [InventoryController::class, 'destroyStock'])->name('inventory.destroy');
        Route::patch('/inventaris/material/{material}', [InventoryController::class, 'updateMaterial'])->name('inventory.material.update');
        Route::delete('/inventaris/material/{material}', [InventoryController::class, 'destroyMaterial'])->name('inventory.material.destroy');

        Route::get('/transfer', [TransferController::class, 'index'])->name('transfers.index');
        Route::post('/transfer/{transaction}/approve', [TransferController::class, 'approve'])->name('transfers.approve');
        Route::post('/transfer/{transaction}/reject', [TransferController::class, 'reject'])->name('transfers.reject');
    });

    // ── Inbound (Kepala Gudang + Mandor) ───────────────────────────
    Route::middleware('role:owner,kepala_gudang,mandor')->group(function () {
        Route::get('/barang-masuk', [InboundController::class, 'index'])->name('inbound.index');
        Route::get('/barang-masuk/baru', [InboundController::class, 'create'])->name('inbound.create');
        Route::post('/barang-masuk', [InboundController::class, 'store'])->name('inbound.store');
    });

    // ── Transfer create (Kepala Gudang) ────────────────────────────
    Route::middleware('role:kepala_gudang')->group(function () {
        Route::get('/transfer/baru', [TransferController::class, 'create'])->name('transfers.create');
        Route::post('/transfer', [TransferController::class, 'store'])->name('transfers.store');
    });

    // ── Projects & Shipments (Owner + Kepala Gudang) ───────────────
    Route::middleware('role:owner,kepala_gudang')->group(function () {
        Route::get('/proyek', [ProjectController::class, 'index'])->name('projects.index');
        Route::post('/proyek', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/proyek/{project}', [ProjectController::class, 'show'])->name('projects.show');

        Route::get('/pengiriman', [ShipmentController::class, 'index'])->name('shipments.index');
        Route::get('/pengiriman/baru', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('/pengiriman', [ShipmentController::class, 'store'])->name('shipments.store');
    });

    // ── Shipment detail & status (Owner + Kepala Gudang + Driver) ─
    Route::middleware('role:owner,kepala_gudang,driver')->group(function () {
        Route::get('/pengiriman/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
        Route::post('/pengiriman/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.status');
    });

    // ── GPS Tracking (Owner) ───────────────────────────────────────
    Route::middleware('role:owner')->group(function () {
        Route::get('/gps-tracking', [GpsController::class, 'index'])->name('gps.index');
        Route::post('/gps-tracking/ping', [GpsController::class, 'ping'])->name('gps.ping');
    });

    // ── Supplier & Purchase Order (Owner + Kepala Gudang) ──────────
    Route::middleware('role:owner,kepala_gudang')->group(function () {
        Route::get('/supplier', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/supplier', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::post('/supplier/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('suppliers.toggle');

        Route::get('/purchase-order', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-order/baru', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-order', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-order/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::get('/purchase-order/{purchaseOrder}/dokumen', [PurchaseOrderController::class, 'document'])->name('purchase-orders.document');
    });

    // ── PO Approval (Owner only) ───────────────────────────────────
    Route::middleware('role:owner')->group(function () {
        Route::post('/purchase-order/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::post('/purchase-order/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
    });

    // ── Reports (Owner + Kepala Gudang) ────────────────────────────
    Route::middleware('role:owner,kepala_gudang')->group(function () {
        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/export', [ReportController::class, 'export'])->name('reports.export');
    });

    // ── Audit Log (Owner only) ─────────────────────────────────────
    Route::middleware('role:owner')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    });

    // ── Settings & User management ─────────────────────────────────
    Route::get('/pengaturan', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/pengaturan/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::middleware('role:owner,kepala_gudang')->group(function () {
        Route::post('/pengaturan/users', [SettingsController::class, 'storeUser'])->name('users.store');
        Route::post('/pengaturan/users/{user}/toggle', [SettingsController::class, 'toggleUser'])->name('users.toggle');
    });
});
