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
        Schema::table('partner_payment_rates', function (Blueprint $table) {
            $table->foreignId('operator_id')
                ->after('partner_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('flow', 10)
                ->after('operator_id')
                ->comment('payin = encaissement, payout = décaissement');

            $table->index(['partner_id', 'operator_id', 'flow', 'status'], 'partner_operator_flow_status_idx');
            $table->index(
                ['partner_id', 'operator_id', 'flow', 'effective_from', 'effective_until'],
                'partner_operator_flow_period_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_payment_rates', function (Blueprint $table) {
            $table->dropIndex('partner_operator_flow_status_idx');
            $table->dropIndex('partner_operator_flow_period_idx');
            $table->dropConstrainedForeignId('operator_id');
            $table->dropColumn('flow');
        });
    }
};
