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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50)->comment('CREATE / UPDATE / DELETE / APPROVE / REJECT');
            $table->string('module', 50)->comment('Modul terkait');
            $table->string('entity_type', 50)->comment('Tipe entitas yang dimodifikasi');
            $table->uuid('entity_id')->comment('ID entitas');
            $table->json('before_data')->nullable()->comment('Data sebelum perubahan');
            $table->json('after_data')->nullable()->comment('Data sesudah perubahan');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['entity_type', 'entity_id']);
            $table->index('user_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
