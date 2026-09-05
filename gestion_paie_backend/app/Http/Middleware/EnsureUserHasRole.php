<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Autorise l'accès uniquement aux utilisateurs possédant l'un des rôles donnés.
     *
     * Utilisation dans les routes :
     *   Route::middleware('role:admin')->group(...)
     *   Route::middleware('role:admin,rh')->group(...)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => "Accès refusé : vous n'avez pas les droits nécessaires.",
            ], 403);
        }

        return $next($request);
    }
}
