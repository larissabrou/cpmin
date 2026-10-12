<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $company = $user->currentCompany;

        if (! $company) {
            $company = $user->companies()->first();
            if ($company) {
                $user->update(['current_company_id' => $company->id]);
            }
        }

        if (! $company) {
            abort(403, 'Aucune entreprise associée à votre compte.');
        }

        $request->attributes->set('company', $company);

        return $next($request);
    }
}
