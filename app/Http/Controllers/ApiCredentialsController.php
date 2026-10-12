<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiCredentialsController extends Controller
{
    public function show(Request $request): View
    {
        $company = $request->attributes->get('company');
        $gatewayUrl = rtrim((string) config('aggregpay.gateway_url'), '/');

        return view('portal.api-credentials', [
            'company' => $company,
            'brand' => config('aggregpay.brand'),
            'gatewayUrl' => $gatewayUrl,
            'apiBaseUrl' => $gatewayUrl.'/api/v1',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $company = $request->attributes->get('company');

        $validated = $request->validate([
            'gateway_webhook_url' => 'nullable|url|max:500',
        ]);

        $company->update([
            'gateway_webhook_url' => $validated['gateway_webhook_url'] ?? null,
        ]);

        return back()->with('success', 'URL webhook enregistrée.');
    }
}
