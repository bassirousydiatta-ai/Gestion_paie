<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CotisationController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Cotisation::class);

        return response()->json(Cotisation::orderBy('nom')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Cotisation::class);

        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'taux' => ['required', 'numeric', 'min:0', 'max:100'],
            'plafond' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json(Cotisation::create($data), 201);
    }

    public function show(Cotisation $cotisation): JsonResponse
    {
        $this->authorize('view', $cotisation);

        return response()->json($cotisation);
    }

    public function update(Request $request, Cotisation $cotisation): JsonResponse
    {
        $this->authorize('update', $cotisation);

        $data = $request->validate([
            'nom' => ['sometimes', 'string', 'max:100'],
            'taux' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'plafond' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cotisation->update($data);

        return response()->json($cotisation->fresh());
    }

    public function destroy(Cotisation $cotisation): JsonResponse
    {
        $this->authorize('delete', $cotisation);

        $cotisation->delete();

        return response()->json(['message' => 'Cotisation supprimée.']);
    }
}
