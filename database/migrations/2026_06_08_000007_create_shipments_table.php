<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained('projects');
            $table->foreignUuid('warehouse_id')->constrained('warehouses');
            $table->foreignUuid('driver_id')->constrained('users');
            $table->string('vehicle_plate', 20);
            $table->enum('status', ['draft', 'confirmed', 'in_transit', 'delivered', 'problem'])->default('draft');
            $table->string('receiver_name', 150)->nullable();
            $table->text('receiver_signature')->nullable()->comment('Tanda tangan digital (base64)');
            $table->decimal('last_gps_lat', 10, 7)->nullable();
            $table->decimal('last_gps_lng', 10, 7)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignUuid('material_id')->constrained('materials');
            $table->decimal('quantity', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};
