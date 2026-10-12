<?php

namespace App\Http\Controllers;

use App\Support\PortalApiDocs;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    public function index(): View
    {
        $gatewayUrl = rtrim((string) config('aggregpay.gateway_url'), '/');
        $company = request()->attributes->get('company');

        return view('portal.documentation', [
            'brand' => config('aggregpay.brand'),
            'apiBaseUrl' => $gatewayUrl.'/api/v1',
            'company' => $company,
            'apiKeyForCurl' => $company?->gateway_api_key ?? 'sk_live_VOTRE_CLE',
            'apiKeyDisplay' => $company?->maskedApiKey() ?? 'sk_live_…',
            'sections' => PortalApiDocs::sections(),
            'navigation' => PortalApiDocs::navigation(),
        ]);
    }
}
