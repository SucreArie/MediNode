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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('node_id');
            $table->string('table_name');
            $table->unsignedBigInteger('record_id');
            $table->enum('operation', ['INSERT', 'UPDATE', 'DELETE']);
            $table->json('data');
            $table->enum('sync_status', ['pending', 'sent', 'acknowledged', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
