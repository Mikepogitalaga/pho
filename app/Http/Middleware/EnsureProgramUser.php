<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProgramUser
{
    /**
     * Restrict non-admin users with a program assignment to a limited set of routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAdmin() && $user->program_id) {
            $allowedRoutePatterns = [
                'dashboard',
                'dashboard.doh',
                'dashboard.gso',
                'items.*',
                'pas.*',
            ];

            $routeName = $request->route()?->getName();

            $isAllowed = false;
            foreach ($allowedRoutePatterns as $pattern) {
                if (fnmatch($pattern, (string) $routeName)) {
                    $isAllowed = true;
                    break;
                }
            }

            abort_unless($isAllowed, 403, 'Your account does not have access to this section.');
        }

        return $next($request);
    }
}
