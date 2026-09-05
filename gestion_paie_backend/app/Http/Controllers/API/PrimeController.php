<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrimeController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Prime::class);

        return response()->json(Prime::orderBy('libelle')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Prime::class);

        $data = $request->validate([
            'libelle' => ['required', 'string', 'max:100'],
            'montant' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['fixe', 'pourcentage'])],
        ]);

        return response()->json(Prime::create($data), 201);
    }

    public function show(Prime $prime): JsonResponse
    {
        $this->authorize('view', $prime);

        return response()->json($prime);
    }

    public function update(Request $request, Prime $prime): JsonResponse
    {
        $this->authorize('update', $prime);

        $data = $request->validate([
            'libelle' => ['sometimes', 'string', 'max:100'],
            'montant' => ['sometimes', 'numeric', 'min:0'],
            'type' => ['sometimes', Rule::in(['fixe', 'pourcentage'])],
        ]);

        $prime->update($data);

        return response()->json($prime->fresh());
    }

    public function destroy(Prime $prime): JsonResponse
    {
        $this->authorize('delete', $prime);

        $prime->delete();

        return response()->json(['message' => 'Prime supprimée.']);
    }
}
