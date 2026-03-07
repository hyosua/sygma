<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Seance;

class SeanceController extends Controller
{
    public function getSeances(){
        $seances = Seance::all();
        return response()->json($seances);
    }

    public function getSeance(Seance $seance){
        return response()->json($seance->load(['cours', 'enseignant', 'groupe']));
    }
}
