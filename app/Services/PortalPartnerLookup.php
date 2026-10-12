<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

class PortalPartnerLookup
{
    /**
     * @return array{name: ?string, email: string, user_id: int, company_id: int}|null
     */
    public function findBySlugAndEmail(string $slug, string $contactEmail): ?array
    {
        $company = Company::query()
            ->where('slug', $slug)
            ->where('email', $contactEmail)
            ->first();

        if (! $company) {
            return null;
        }

        $user = $company->users()
            ->wherePivot('is_owner', true)
            ->first();

        if (! $user && filled($company->email)) {
            $user = User::query()->where('email', $company->email)->first();
        }

        if (! $user) {
            return null;
        }

        return [
            'name' => filled($user->name) ? $user->name : null,
            'email' => $user->email,
            'user_id' => $user->id,
            'company_id' => $company->id,
        ];
    }
}
