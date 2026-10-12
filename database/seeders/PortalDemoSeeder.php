<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['slug' => 'composuite'],
            [
                'name' => 'CompoSuite',
                'email' => 'contact@composuite.local',
                'status' => 'active',
                'gateway_api_key' => env('AGGREGPAY_API_KEY'),
                'gateway_webhook_url' => env('AGGREGPAY_WEBHOOK_URL'),
                'gateway_webhook_secret' => env('AGGREGPAY_WEBHOOK_SECRET'),
            ],
        );

        $user = User::updateOrCreate(
            ['email' => 'admin@composuite.local'],
            [
                'name' => 'Admin CompoSuite',
                'password' => Hash::make(env('PORTAL_DEMO_PASSWORD', 'password')),
                'current_company_id' => $company->id,
            ],
        );

        $company->users()->syncWithoutDetaching([
            $user->id => ['role' => 'owner', 'is_owner' => true, 'joined_at' => now()],
        ]);
    }
}
