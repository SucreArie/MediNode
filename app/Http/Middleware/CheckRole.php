<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Expected to be used like: ->middleware('role:admin,doctor')
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        if (empty($roles)) {
            return response()->json(['message' => 'Rôle requis'], 403);
        }

        // support comma-separated single string
        if (count($roles) === 1 && strpos($roles[0], ',') !== false) {
            $roles = array_map('trim', explode(',', $roles[0]));
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Accès refusé pour votre rôle'], 403);
        }

        return $next($request);
    }
}
