<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyContextController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $company = $user->companies()
            ->where('companies.id', $validated['company_id'])
            ->first();

        if (! $company) {
            abort(403, 'Projet inaccessible.');
        }

        if ($user->current_company_id !== $company->id) {
            $user->update(['current_company_id' => $company->id]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Projet actif : '.$company->name.'.');
    }
}
