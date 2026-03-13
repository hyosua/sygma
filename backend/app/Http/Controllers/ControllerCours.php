<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use Illuminate\Http\Request;

class ControllerCours extends Controller
{
    public function getCours()
    {
        $cours = Cours::all();

        return $cours;
    }

    public function createCours(Request $req)
    {

        $lib = $req->input('nom');

        $verif = Cours::where('nom', $lib)->get();

        foreach ($verif as $v) {
            if ($v->nom) {
                return response()->json(['message' => 'Le cours existe déjà'], 409);
            }
        }

        $cours = Cours::create([
            'nom' => $lib,
        ]);

        return response()->json($cours, 201);
    }

    public function updateCours(Request $req, $id)
    {
        $cours = Cours::find($id);

        if ($cours) {
            $lib = $req->input('nom');

            if ($lib) {
                $verif2 = Cours::where('nom', $lib)->get();

                foreach ($verif2 as $v2) {
                    if ($v2->nom) {
                        return response()->json(['message' => 'Le cours existe déjà'], 409);
                    }
                }

                $cours->nom = $lib;
                $cours->Save();

                return response()->json($cours, 200);
            }

            return response()->json('il faut un libelé', 400);
        }

        return response()->json('cours introuvable', 404);
    }

    public function deleteCours($id)
    {
        $cours = Cours::find($id);

        if ($cours) {
            $cours->delete();

            return response()->json('Le cours à bien été supprimé', 200);
        }

        return response()->json("Le cours n'a pas été trouver", 404);
    }
}
