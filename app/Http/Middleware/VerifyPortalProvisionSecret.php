<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPortalProvisionSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('portal.provision_secret');

        if (! filled($secret)) {
            return response()->json([
                'success' => false,
                'message' => 'Provisioning portail non configuré.',
            ], 503);
        }

        $token = $request->bearerToken();

        if (! filled($token) || ! hash_equals($secret, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé.',
            ], 401);
        }

        return $next($request);
    }
}
