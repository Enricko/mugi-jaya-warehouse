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
        Schema::create('warehouse_stock', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignUuid('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->nullOnDelete()
                  ->comment('Tag proyek (NULL = stok umum)');
            $table->decimal('quantity', 15, 2)->default(0);
            $table->string('location_tag', 50)->nullable()->comment('Misal: Rak A3, Blok B');
            $table->timestamp('updated_at')->nullable();

            $table->unique(['warehouse_id', 'material_id', 'project_id'], 'warehouse_stock_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock');
    }
};
