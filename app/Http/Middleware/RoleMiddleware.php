<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Allow access only when the authenticated API user's
     * current role matches one of the required role slugs.
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | 1. Get the authenticated JWT user
        |--------------------------------------------------------------------------
        */

        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Make sure the user account is still active
        |--------------------------------------------------------------------------
        */

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently inactive.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Load the user's current role from the database
        |--------------------------------------------------------------------------
        */

        $user->loadMissing('role');

        if (! $user->role) {
            return response()->json([
                'success' => false,
                'message' => 'No role is assigned to this account.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Make sure the assigned role itself is active
        |--------------------------------------------------------------------------
        */

        if (! $user->role->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your assigned role is currently inactive.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Normalize allowed role slugs
        |--------------------------------------------------------------------------
        */

        $allowedRoles = array_map(
            static fn (string $role): string => strtoupper(trim($role)),
            $roles
        );

        /*
        |--------------------------------------------------------------------------
        | 6. Check whether the user's role is allowed
        |--------------------------------------------------------------------------
        */

        $currentRole = strtoupper($user->role->slug);

        if (! in_array($currentRole, $allowedRoles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to access this resource.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. User has permission
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}