<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes: ->middleware('role:students') or ->middleware('role:instructor')
 * If the logged-in user has a different role, send them to their OWN home page
 * instead of showing an error.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user->role->value !== $role) {
            return redirect($user->role->homePath());
        }

        return $next($request);
    }
}
