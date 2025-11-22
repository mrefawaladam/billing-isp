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
        if (Schema::hasTable('wa_notifications')) {
            if (!Schema::hasColumn('wa_notifications', 'phone')) {
                Schema::table('wa_notifications', function (Blueprint $table) {
                    $table->string('phone', 20)->nullable()->after('customer_id');
                });
            }
            if (!Schema::hasColumn('wa_notifications', 'error_message')) {
                Schema::table('wa_notifications', function (Blueprint $table) {
                    $table->text('error_message')->nullable()->after('provider_response');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('wa_notifications')) {
            if (Schema::hasColumn('wa_notifications', 'phone')) {
                Schema::table('wa_notifications', function (Blueprint $table) {
                    $table->dropColumn('phone');
                });
            }
            if (Schema::hasColumn('wa_notifications', 'error_message')) {
                Schema::table('wa_notifications', function (Blueprint $table) {
                    $table->dropColumn('error_message');
                });
            }
        }
    }
};
