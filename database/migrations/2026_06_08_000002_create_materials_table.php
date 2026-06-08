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
        Schema::create('materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku', 50)->unique();
            $table->string('name', 150);
            $table->string('unit', 20)->comment('Satuan: m, kg, lembar, dll');
            $table->decimal('purchase_price', 15, 2)->default(0)->comment('Harga beli untuk valuasi');
            $table->integer('min_stock')->default(0)->comment('Batas minimum untuk alert');
            $table->string('category', 50)->nullable()->comment('Aluminium / Kaca / Aksesori');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
