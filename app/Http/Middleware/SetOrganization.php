<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if organization is specified in header (for API)
            if ($request->header('X-Organization-ID')) {
                $orgId = $request->header('X-Organization-ID');
                $organization = $user->organizations()->find($orgId);
                
                if ($organization) {
                    session(['current_organization_id' => $orgId]);
                } else {
                    return response()->json([
                        'error' => 'Invalid organization or access denied'
                    ], 403);
                }
            }
            
            // If no organization is set in session, set the first one
            if (!session('current_organization_id')) {
                $firstOrg = $user->organizations()->first();
                if ($firstOrg) {
                    session(['current_organization_id' => $firstOrg->id]);
                }
            }
            
            // Verify user has access to current organization
            $currentOrgId = session('current_organization_id');
            if ($currentOrgId) {
                $hasAccess = $user->organizations()->where('organizations.id', $currentOrgId)->exists();
                if (!$hasAccess) {
                    // User lost access, switch to first available organization
                    $firstOrg = $user->organizations()->first();
                    if ($firstOrg) {
                        session(['current_organization_id' => $firstOrg->id]);
                    } else {
                        session()->forget('current_organization_id');
                    }
                }
            }
        }
        
        return $next($request);
    }
}
