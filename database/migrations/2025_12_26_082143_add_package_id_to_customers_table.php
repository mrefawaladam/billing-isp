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
            $table->uuid('package_id')->nullable()->after('monthly_fee');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
            $table->index('package_id');
            $table->boolean('use_custom_price')->default(false)->after('package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropIndex(['package_id']);
            $table->dropColumn(['package_id', 'use_custom_price']);
        });
    }
};
