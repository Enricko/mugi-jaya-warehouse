<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add structured fields so transfers/inbound/consumption carry the
     * warehouse, material and quantity they affect. Kept nullable because
     * not every transaction type uses every field.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignUuid('from_warehouse_id')->nullable()->after('reference_id')->constrained('warehouses')->nullOnDelete();
            $table->foreignUuid('to_warehouse_id')->nullable()->after('from_warehouse_id')->constrained('warehouses')->nullOnDelete();
            $table->foreignUuid('material_id')->nullable()->after('to_warehouse_id')->constrained('materials')->nullOnDelete();
            $table->foreignUuid('project_id')->nullable()->after('material_id')->constrained('projects')->nullOnDelete();
            $table->decimal('quantity', 15, 2)->nullable()->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('from_warehouse_id');
            $table->dropConstrainedForeignId('to_warehouse_id');
            $table->dropConstrainedForeignId('material_id');
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn('quantity');
        });
    }
};
