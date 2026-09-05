<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contrat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContratController extends Controller
{
    /**
     * GET /api/contrats?employe_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Contrat::class);

        $contrats = Contrat::query()
            ->with(['employe', 'poste.departement'])
            ->when($request->filled('employe_id'), fn ($q) => $q->where('employe_id', $request->integer('employe_id')))
            ->orderByDesc('date_debut')
            ->paginate($request->integer('par_page', 15));

        return response()->json($contrats);
    }

    /**
     * POST /api/contrats
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Contrat::class);

        $data = $request->validate([
            'employe_id' => ['required', 'exists:employes,id'],
            'poste_id' => ['required', 'exists:postes,id'],
            'type_contrat' => ['required', Rule::in(['CDI', 'CDD', 'Stage', 'Interim'])],
            'salaire_base' => ['required', 'numeric', 'min:0'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_debut'],
        ]);

        $contrat = Contrat::create($data);

        return response()->json($contrat->load('poste.departement'), 201);
    }

    /**
     * GET /api/contrats/{contrat}
     */
    public function show(Contrat $contrat): JsonResponse
    {
        $this->authorize('view', $contrat);

        return response()->json($contrat->load(['employe', 'poste.departement']));
    }

    /**
     * PUT/PATCH /api/contrats/{contrat}
     */
    public function update(Request $request, Contrat $contrat): JsonResponse
    {
        $this->authorize('update', $contrat);

        $data = $request->validate([
            'poste_id' => ['sometimes', 'exists:postes,id'],
            'type_contrat' => ['sometimes', Rule::in(['CDI', 'CDD', 'Stage', 'Interim'])],
            'salaire_base' => ['sometimes', 'numeric', 'min:0'],
            'date_debut' => ['sometimes', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_debut'],
        ]);

        $contrat->update($data);

        return response()->json($contrat->fresh(['poste.departement']));
    }

    /**
     * DELETE /api/contrats/{contrat}
     */
    public function destroy(Contrat $contrat): JsonResponse
    {
        $this->authorize('delete', $contrat);

        $contrat->delete();

        return response()->json(['message' => 'Contrat supprimé.']);
    }
}
