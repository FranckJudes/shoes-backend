<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminMiddleware
{
    // Vérifier si l'utilisateur est authentifié
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Non authentifié. Veuillez vous connecter.',
                'success' => false
            ], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Accès non autorisé. Vous n\'avez pas les permissions d\'administrateur.',
                'success' => false,
                'required_role' => 'admin',
                'user_role' => $user->role
            ], 403);
        }

        return $next($request);
    }
}