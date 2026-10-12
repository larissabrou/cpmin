<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('gateway_api_key')->nullable()->after('settings');
            $table->string('gateway_webhook_url')->nullable()->after('gateway_api_key');
            $table->text('gateway_webhook_secret')->nullable()->after('gateway_webhook_url');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['gateway_api_key', 'gateway_webhook_url', 'gateway_webhook_secret']);
        });
    }
};
