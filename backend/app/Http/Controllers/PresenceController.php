<?php


namespace App\Http\Controllers;

use App\Models\Presence;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;



class PresenceController extends Controller{




    function getPresenceById(User $user, Request $request){

        $types = $request->input('statuts') ;


        if(empty($types) or is_null($types)){ return response()->json("Il manque le statuts", 406);}
        if(!$user->id){return response()->json("Utilisateur introuvable", 404);}

        if($types == "A" or $types == "a"){
        $presences = DB::table('presences')
        ->join('users', 'presences.etudiant_id', '=', 'users.id')
        ->join('sessions_emargement', 'presences.session_emargement_id', '=', 'sessions_emargement.id')
        ->join('seances', 'sessions_emargement.seance_id', '=', 'seances.id')
        ->join('cours', 'seances.cours_id', '=', 'cours.id')
        ->where('presences.etudiant_id', $user->id)
        ->where('presences.statut', 'absent')
        ->select('cours.nom','presences.statut', DB::raw("TO_CHAR(presences.created_at, 'FMMonth') as mois")
    )->paginate(10);
        }elseif($types == "P" or $types == "p"){
            $presences = DB::table('presences')
        ->join('users', 'presences.etudiant_id', '=', 'users.id')
        ->join('sessions_emargement', 'presences.session_emargement_id', '=', 'sessions_emargement.id')
        ->join('seances', 'sessions_emargement.seance_id', '=', 'seances.id')
        ->join('cours', 'seances.cours_id', '=', 'cours.id')
        ->where('presences.etudiant_id', $user->id)
        ->where('presences.statut', 'present')
        ->select('cours.nom','presences.statut', DB::raw("TO_CHAR(presences.created_at, 'FMMonth') as mois")
            )->paginate(10);
        }else{
            return response()->json("Aucun type défini: veuillez choisir entre A pour absent ou P pour présent", 401);
        }

        return response()->json($presences, 200);
      

    }
}