<?php

namespace App\Services;

use App\Models\Company;

class PortalPartnerLifecycle
{
    public function findCompany(string $slug, string $contactEmail): Company
    {
        return Company::query()
            ->where('slug', $slug)
            ->where('email', $contactEmail)
            ->firstOrFail();
    }

    public function activate(string $slug, string $contactEmail): void
    {
        $this->findCompany($slug, $contactEmail)->update(['status' => 'active']);
    }

    public function deactivate(string $slug, string $contactEmail): void
    {
        $this->findCompany($slug, $contactEmail)->update(['status' => 'inactive']);
    }

    public function delete(string $slug, string $contactEmail): void
    {
        $company = $this->findCompany($slug, $contactEmail);
        $company->users()->detach();
        $company->delete();
    }
}
