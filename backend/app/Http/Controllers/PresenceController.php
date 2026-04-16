<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{
    public function getPresenceById(User $user, Request $request)
    {
        if (auth()->id() !== $user->id) {
            return response()->json('Accès non autorisé', 403);
        }

        $types = $request->input('statuts');

        if (empty($types)) {
            return response()->json('Il manque le statuts', 406);
        }

        if ($types == 'A' || $types == 'a') {
            $presences = DB::table('presences')
                ->join('users', 'presences.etudiant_id', '=', 'users.id')
                ->join('sessions_emargement', 'presences.session_emargement_id', '=', 'sessions_emargement.id')
                ->join('seances', 'sessions_emargement.seance_id', '=', 'seances.id')
                ->join('cours', 'seances.cours_id', '=', 'cours.id')
                ->where('presences.etudiant_id', $user->id)
                ->where('presences.statut', 'absent')
                ->select('cours.nom', 'presences.statut', DB::raw("TO_CHAR(presences.created_at, 'FMMonth') as mois"))
                ->paginate(10);
        } elseif ($types == 'P' || $types == 'p') {
            $presences = DB::table('presences')
                ->join('users', 'presences.etudiant_id', '=', 'users.id')
                ->join('sessions_emargement', 'presences.session_emargement_id', '=', 'sessions_emargement.id')
                ->join('seances', 'sessions_emargement.seance_id', '=', 'seances.id')
                ->join('cours', 'seances.cours_id', '=', 'cours.id')
                ->where('presences.etudiant_id', $user->id)
                ->where('presences.statut', 'present')
                ->select('cours.nom', 'presences.statut', DB::raw("TO_CHAR(presences.created_at, 'FMMonth') as mois"))
                ->paginate(10);
        } else {
            return response()->json('Aucun type défini: veuillez choisir entre A pour absent ou P pour présent', 422);
        }

        return response()->json($presences, 200);
    }
}
