<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PortalPartnerLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerLifecycleController extends Controller
{
    public function destroy(Request $request, string $slug, PortalPartnerLifecycle $lifecycle): JsonResponse
    {
        $validated = $request->validate([
            'contact_email' => 'required|email|max:190',
        ]);

        $lifecycle->delete($slug, $validated['contact_email']);

        return response()->json([
            'success' => true,
            'message' => 'Projet portail supprimé.',
        ]);
    }

    public function activate(Request $request, string $slug, PortalPartnerLifecycle $lifecycle): JsonResponse
    {
        $validated = $request->validate([
            'contact_email' => 'required|email|max:190',
        ]);

        $lifecycle->activate($slug, $validated['contact_email']);

        return response()->json([
            'success' => true,
            'message' => 'Projet portail activé.',
        ]);
    }

    public function deactivate(Request $request, string $slug, PortalPartnerLifecycle $lifecycle): JsonResponse
    {
        $validated = $request->validate([
            'contact_email' => 'required|email|max:190',
        ]);

        $lifecycle->deactivate($slug, $validated['contact_email']);

        return response()->json([
            'success' => true,
            'message' => 'Projet portail désactivé.',
        ]);
    }
}
