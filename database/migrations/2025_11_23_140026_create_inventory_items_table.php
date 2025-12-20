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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique()->nullable();
            $table->string('name', 255)->nullable(false);
            $table->string('type', 50)->nullable(false); // router, ont, kabel, connector, dll
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->text('description')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(0); // Alert jika stock <= min_stock
            $table->string('unit', 20)->default('pcs'); // pcs, meter, roll, dll
            $table->decimal('price', 12, 2)->default(0);
            $table->string('location', 255)->nullable(); // Lokasi penyimpanan
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
