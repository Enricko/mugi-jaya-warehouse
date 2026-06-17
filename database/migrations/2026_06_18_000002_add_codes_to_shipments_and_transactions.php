<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Human-readable reference codes shown in the UI
     * (e.g. SHP-0894 for shipments, TRF-0089 for transfer transactions).
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->unique()->after('id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
