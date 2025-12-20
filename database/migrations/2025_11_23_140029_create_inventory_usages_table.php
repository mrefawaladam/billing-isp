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
        Schema::create('inventory_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
            $table->uuid('customer_id')->nullable();
            $table->uuid('device_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('usage_type', 50)->nullable(false); // installed, returned, maintenance, damaged, lost
            $table->uuid('used_by')->nullable(); // User yang menggunakan
            $table->timestamp('used_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('set null');
            $table->foreign('used_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('inventory_item_id');
            $table->index('customer_id');
            $table->index('usage_type');
            $table->index('used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_usages');
    }
};
