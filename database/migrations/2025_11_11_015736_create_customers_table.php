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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('customer_code', 50)->unique()->nullable();
            $table->string('name', 255)->nullable(false);
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('type', 50)->default('rumahan');
            $table->boolean('active')->default(true);
            $table->uuid('assigned_to')->nullable();
            $table->decimal('monthly_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->boolean('ppn_included')->default(false);
            $table->decimal('total_fee', 12, 2)->default(0);
            $table->integer('invoice_due_day')->default(1);
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
