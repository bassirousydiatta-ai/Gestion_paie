<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartementController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Departement::class);

        return response()->json(
            Departement::withCount('postes')->orderBy('nom')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Departement::class);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', 'unique:departements,nom'],
        ]);

        return response()->json(Departement::create($data), 201);
    }

    public function show(Departement $departement): JsonResponse
    {
        $this->authorize('view', $departement);

        return response()->json($departement->load('postes'));
    }

    public function update(Request $request, Departement $departement): JsonResponse
    {
        $this->authorize('update', $departement);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100', Rule::unique('departements', 'nom')->ignore($departement->id)],
        ]);

        $departement->update($data);

        return response()->json($departement->fresh());
    }

    public function destroy(Departement $departement): JsonResponse
    {
        $this->authorize('delete', $departement);

        if ($departement->postes()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer un département qui contient encore des postes.',
            ], 409);
        }

        $departement->delete();

        return response()->json(['message' => 'Département supprimé.']);
    }
}
