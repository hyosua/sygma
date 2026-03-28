<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Services\EmargementService;
use App\Models\Seance;
use App\Models\SessionEmargement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // AJOUTEZ CETTE LIGNE
use App\Exceptions\JetonInvalideException;
use App\Exceptions\JetonExpireException;
use App\Exceptions\SeanceNonActiveException;
use App\Exceptions\DejaEmargeException;
use Carbon\Carbon;
use Exception;

class ExportController extends Controller
{
    function getSessionByDate(Request $request)
    {
        // Vérifier si la date est fournie
        if (!$request->has('date')) {
            return response()->json([
                'error' => 'Date manquante',
                'message' => 'Veuillez fournir une date au format YYYY-MM-DD'
            ], 400);
        }

        try {
            $date = Carbon::parse($request->input("date"))->format('Y-m-d');

            $presences = DB::table('presences as p')
                ->join('sessions_emargement as se', 'p.session_emargement_id', '=', 'se.id')
                ->join('users as u', 'p.etudiant_id', '=', 'u.id')
                ->whereDate('p.created_at', $date)
                ->select(
                    'u.id as user_id',
                    'u.nom as user_nom',           // Ajout du nom
                    'u.prenom as user_prenom',      // Ajout du prénom
                    'u.email as user_email',
                    'u.created_at as user_created_at',
                    'u.updated_at as user_updated_at'

                )
                ->get();

            if ($presences->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'date' => $date,
                    'count' => $presences->count(),
                    'data' => $presences
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun étudiant trouvé pour cette date',
                    'date' => $date
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du traitement',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
