<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:8|confirmed'
        ]);

        $user = User::create([
            'name'=>$validated['name'],
            'email'=>$validated['email'],
            'password'=>Hash::make($validated['password'])
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'=>$user,
            'token'=>$token
        ],201);
    }
    /**
     * Connexion : vérifie les identifiants et retourne un token Bearer.
     *
     * POST /api/login
     * Body: { "email": "...", "password": "..." }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            // On lève une ValidationException plutôt qu'un message générique,
            // pour rester cohérent avec le format d'erreurs Laravel (422).
            throw ValidationException::withMessages([
                'email' => ["Les identifiants fournis sont incorrects."],
            ]);
        }

        /** @var User $user **/
        $user = User::where('email', $credentials['email'])->firstOrFail();

        // Un seul token actif à la fois : on révoque les anciens tokens
        // (optionnel — retirez cette ligne si vous voulez autoriser
        // plusieurs sessions simultanées, ex. web + mobile).
        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('employe'),
            'token' => $token,
        ]);
    }

    /**
     * Déconnexion : révoque uniquement le token utilisé pour cette requête.
     *
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Retourne l'utilisateur actuellement authentifié.
     *
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->load('employe')
        );
    }
}
