<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PortalPartnerProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PartnerProvisionController extends Controller
{
    public function store(Request $request, PortalPartnerProvisioner $provisioner): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:60|alpha_dash',
            'name' => 'required|string|max:120',
            'contact_email' => 'required|email|max:190',
            'api_key' => 'required|string|max:64',
            'webhook_url' => 'nullable|url|max:500',
            'webhook_secret' => 'nullable|string|max:80',
        ]);

        Log::info('Internal partner provision API called', [
            'slug' => $validated['slug'],
            'contact_email' => $validated['contact_email'],
            'ip' => $request->ip(),
        ]);

        try {
            $result = $provisioner->provision($validated);

            Log::info('Internal partner provision API succeeded', [
                'slug' => $validated['slug'],
                'contact_email' => $validated['contact_email'],
                ...$result,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Accès portail créés et e-mail envoyé.',
                ...$result,
            ], 201);
        } catch (Throwable $e) {
            Log::error('Internal partner provision API failed', [
                'slug' => $validated['slug'],
                'contact_email' => $validated['contact_email'],
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec provisioning portail : '.$e->getMessage(),
            ], 500);
        }
    }
}
