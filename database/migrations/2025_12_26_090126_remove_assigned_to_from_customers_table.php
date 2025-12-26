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
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropIndex(['assigned_to']);
            $table->dropColumn('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('assigned_to')->nullable()->after('active');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index('assigned_to');
        });

        // Migrate back from pivot table (take first assigned user)
        \Illuminate\Support\Facades\DB::statement("
            UPDATE customers c
            INNER JOIN (
                SELECT customer_id, user_id
                FROM customer_user
                ORDER BY created_at ASC
            ) cu ON c.id = cu.customer_id
            SET c.assigned_to = cu.user_id
        ");
    }
};
