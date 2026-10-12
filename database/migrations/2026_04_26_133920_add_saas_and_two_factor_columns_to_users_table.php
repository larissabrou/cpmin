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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->nullOnDelete();

            $table->boolean('two_factor_enabled')->default(false)->after('remember_token');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_enabled');
            $table->string('two_factor_method')->nullable()->after('two_factor_confirmed_at');
            $table->text('two_factor_secret')->nullable()->after('two_factor_method');
            $table->json('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_company_id');
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_confirmed_at',
                'two_factor_method',
                'two_factor_secret',
                'two_factor_recovery_codes',
            ]);
        });
    }
};
