<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_user', function (Blueprint $table) {
            $table->uuid('customer_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->primary(['customer_id', 'user_id']);
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('customer_id');
            $table->index('user_id');
        });

        // Migrate existing assigned_to data to pivot table
        DB::statement("
            INSERT INTO customer_user (customer_id, user_id, created_at, updated_at)
            SELECT id, assigned_to, NOW(), NOW()
            FROM customers
            WHERE assigned_to IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_user');
    }
};
