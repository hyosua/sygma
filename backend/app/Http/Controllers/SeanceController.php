<?php

namespace App\Http\Controllers;

use App\Models\Seance;

class SeanceController extends Controller
{
    public function getSeances()
    {
        $seances = Seance::all();

        return response()->json($seances);
    }

    public function getSeance(Seance $seance)
    {
        $seance->load(['cours', 'enseignant', 'groupe.users']);
        $seance->nombre_inscrits = $seance->groupe?->users->count() ?? 0;

        return response()->json($seance);
    }
}
