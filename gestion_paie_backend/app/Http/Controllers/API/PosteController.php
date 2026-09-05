<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poste;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Poste::class);

        $postes = Poste::query()
            ->with('departement')
            ->when($request->filled('departement_id'), fn ($q) => $q->where('departement_id', $request->integer('departement_id')))
            ->orderBy('intitule')
            ->get();

        return response()->json($postes);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Poste::class);

        $data = $request->validate([
            'departement_id' => ['required', 'exists:departements,id'],
            'intitule' => ['required', 'string', 'max:100'],
        ]);

        return response()->json(Poste::create($data)->load('departement'), 201);
    }

    public function show(Poste $poste): JsonResponse
    {
        $this->authorize('view', $poste);

        return response()->json($poste->load('departement'));
    }

    public function update(Request $request, Poste $poste): JsonResponse
    {
        $this->authorize('update', $poste);

        $data = $request->validate([
            'departement_id' => ['sometimes', 'exists:departements,id'],
            'intitule' => ['sometimes', 'string', 'max:100'],
        ]);

        $poste->update($data);

        return response()->json($poste->fresh('departement'));
    }

    public function destroy(Poste $poste): JsonResponse
    {
        $this->authorize('delete', $poste);

        if ($poste->contrats()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer un poste encore utilisé par des contrats.',
            ], 409);
        }

        $poste->delete();

        return response()->json(['message' => 'Poste supprimé.']);
    }
}
