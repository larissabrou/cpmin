<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PortalPartnerLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerLookupController extends Controller
{
    public function show(Request $request, string $slug, PortalPartnerLookup $lookup): JsonResponse
    {
        $validated = $request->validate([
            'contact_email' => 'required|email|max:190',
        ]);

        $user = $lookup->findBySlugAndEmail($slug, $validated['contact_email']);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun compte portail trouvé pour ce projet.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }
}
