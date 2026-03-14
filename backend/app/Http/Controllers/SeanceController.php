<?php

namespace App\Http\Controllers;

use App\Http\Resources\SeanceResource;
use App\Models\Seance;
use App\Services\SeanceService;
use Illuminate\Http\Request;

class SeanceController extends Controller
{
    public function __construct(private SeanceService $seanceService)
    {
    }

    public function getSeances(Request $request)
    {
        $seances = $this->seanceService->getSeances($request->only(
            ['enseignant_id', 'groupe_id', 'cours_id', 'date_debut', 'date_fin', 'statut', 'par_page']
        ));

        return SeanceResource::collection($seances);
    }

    public function getSeance(Seance $seance)
    {
        $seance = $this->seanceService->getSeance($seance);

        return new SeanceResource($seance);
    }

    public function supprimer(Seance $seance)
    {
        $this->seanceService->supprimerSeance($seance);

        return response()->json(['message' => "La séance $seance->id a bien été supprimée"], 204);
    }

    public function creerSeance(Request $request)
    {
        $data = $request->validate([
            'cours_id' => 'required|exists:cours,id',
            'enseignant_id' => 'required|exists:users,id',
            'groupe_id' => 'required|exists:groupes,id',
            'debut_a' => 'required|date',
            'fin_a' => 'required|date|after:debut_a',
            'salle' => 'nullable|integer',
        ]);

        $result = $this->seanceService->creerSeance($data);

        return response()->json($result, 201);
    }
}
