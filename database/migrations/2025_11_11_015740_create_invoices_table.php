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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->integer('year')->nullable(false);
            $table->integer('month')->nullable(false);
            $table->string('invoice_number', 100)->unique()->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('amount_before_tax', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('UNPAID');
            $table->integer('months_overdue')->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->uuid('generated_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['customer_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
