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
        Schema::create('partner_payment_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->decimal('commission_percent', 8, 4)->default(0);
            $table->decimal('fixed_fee_per_transaction', 12, 2)->nullable();
            $table->decimal('min_transaction_amount', 12, 2)->nullable();
            $table->decimal('max_transaction_amount', 12, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->timestamp('effective_from');
            $table->timestamp('effective_until')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'status'], 'ppr_partner_status_idx');
            $table->index(['partner_id', 'effective_from', 'effective_until'], 'ppr_partner_period_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_payment_rates');
    }
};
