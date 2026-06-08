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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('message')->nullable();
            $table->string('type', 50)->nullable()->comment('alert / info / warning');
            $table->string('module', 50)->nullable()->comment('Modul terkait');
            $table->uuid('entity_id')->nullable();
            $table->string('entity_type', 50)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('wa_sent')->default(false)->comment('Status pengiriman WhatsApp');
            $table->timestamp('wa_sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
