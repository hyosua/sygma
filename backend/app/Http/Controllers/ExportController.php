<?php

namespace App\Http\Controllers;

use App\Services\EmargementService;
use App\Models\Seance;
use App\Models\SessionEmargement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\JetonInvalideException;
use App\Exceptions\JetonExpireException;
use App\Exceptions\SeanceNonActiveException;
use App\Exceptions\DejaEmargeException;
use Carbon\Carbon;
use Exception;

class ExportController extends Controller
{
    function getSessionByDate(Request $request){

        $date = Carbon::parse($request->input("date"))->format('Y-m-d H:i:s');

        $presences = Presence::join('sessions_emargement', 'presences.session_emargement_id', '=', 'sessions_emargement.id')
        ->where('presences.created_at', $date)
        ->select('presences.*', 'sessions_emargement.*')
        ->get();

        if ($presences){
             return response()->json($presences, 200);
        }else{
            return response()->json("Aucun étudiant n'a été absent à cette date", 200);
        }
    }
}
