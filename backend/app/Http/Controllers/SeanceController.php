<?php

namespace App\Http\Controllers;

use App\Exceptions\NonAutoriseException;
use App\Http\Resources\SeanceResource;
use App\Models\Seance;
use App\Services\SeanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeanceController extends Controller
{
    public function __construct(private SeanceService $seanceService)
    {
    }

    public function getSeances(Request $request)
    {
        $seances = $this->seanceService->getSeances(
            $request->only(['enseignant_id', 'groupe_id', 'cours_id', 'date_debut', 'date_fin', 'statut', 'par_page']),
            $request->user()
        );

        return SeanceResource::collection($seances);
    }

    public function getSeance(Seance $seance)
    {
        $seance = $this->seanceService->getSeance($seance);

        return new SeanceResource($seance);
    }

    public function getSessions(Seance $seance)
    {
        $result = $this->seanceService->getSessions($seance);

        return response()->json($result, 200);
    }

    public function supprimer(Seance $seance)
    {
        if ($seance->enseignant_id !== Auth::id()) {
            throw new NonAutoriseException();
        }

        $this->seanceService->supprimerSeance($seance);

        return response()->noContent();
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

        return new SeanceResource($result);
    }

    public function modifierSeance(Seance $seance, Request $request)
    {
        if ($seance->enseignant_id !== Auth::id()) {
            throw new NonAutoriseException();
        }

        $data = $request->validate([
            'cours_id' => 'required|exists:cours,id',
            'enseignant_id' => 'required|exists:users,id',
            'groupe_id' => 'required|exists:groupes,id',
            'debut_a' => 'required|date',
            'fin_a' => 'required|date|after:debut_a',
            'salle' => 'nullable|integer',
        ]);

        $seance = $this->seanceService->modifierSeance($seance, $data);

        return new SeanceResource($seance);
    }
}
