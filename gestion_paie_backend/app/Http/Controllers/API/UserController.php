<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/users
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return response()->json(
            User::with('employe')->orderBy('name')->get()
        );
    }

    /**
     * POST /api/users
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'employe_id' => ['nullable', 'exists:employes,id'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'rh', 'comptable'])],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user->load('employe'), 201);
    }

    /**
     * GET /api/users/{user}
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json($user->load('employe'));
    }

    /**
     * PUT/PATCH /api/users/{user}
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'employe_id' => ['nullable', 'exists:employes,id'],
            'name' => ['sometimes', 'string', 'max:150'],
            'email' => ['sometimes', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            // Seul l'Admin peut changer un rôle (un utilisateur ne peut pas s'auto-promouvoir)
            'role' => ['sometimes', Rule::in(['admin', 'rh', 'comptable'])],
        ]);

        if (isset($data['role']) && $request->user()->role !== 'admin') {
            unset($data['role']);
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user->fresh('employe'));
    }

    /**
     * DELETE /api/users/{user}
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }
}
