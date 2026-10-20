<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProgramUser
{
    /**
     * Restrict non-admin users with a program assignment to a limited set of routes.
     *
     * Receivings and releases are visible read-only: a program user may list,
     * open and print those records for their own program, but nothing that
     * creates or changes them.
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
                'profile.*',
                // Receivings — read-only routes only.
                'receivings.index',
                'receivings.view',
                'receivings.print',
                'receivings.print-list',
                'receivings.export',
                // Releases — read-only routes only.
                'releases.index',
                'releases.view',
                'releases.print',
                'releases.print-list',
                'releases.export',
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
