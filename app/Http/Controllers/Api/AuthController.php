<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login: validate username+password, attempt auth, return user + anggota.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // Regenerate session for security (prevents fixation)
        $request->session()->regenerate();

        $user = Auth::guard('web')->user();
        $anggota = $user->role === 'anggota' ? $user->anggota : null;

        return response()->json([
            'user' => [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'anggota' => $anggota,
        ]);
    }

    /**
     * Logout: destroy session, return success message.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Me: return current user + anggota (if any).
     */
    public function me(Request $request): JsonResponse
    {
        $user = Auth::guard('web')->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Load anggota if role=anggota
        $anggota = $user->role === 'anggota' ? $user->anggota()->first() : null;

        return response()->json([
            'user' => [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'role' => $user->role,
            ],
            'anggota' => $anggota,
        ]);
    }
}
