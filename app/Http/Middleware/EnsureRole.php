<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * Usage: Route::middleware(['auth:web', 'role:admin'])->group(...)
     * Multiple roles: 'role:admin,anggota' means user.role must be admin OR anggota
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak memiliki hak untuk tindakan ini.',
            ], 403);
        }

        return $next($request);
    }
}
