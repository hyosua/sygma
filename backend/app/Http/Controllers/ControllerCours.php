<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Services\CoursService;
use Illuminate\Http\Request;

class ControllerCours extends Controller
{
    public function __construct(private CoursService $coursService)
    {
    }

    public function getCours()
    {
        return response()->json($this->coursService->getCours(), 200);
    }

    public function createCours(Request $req)
    {

        $nom = $req->input('nom');

        if (! $nom) {
            return response()->json('il faut un nom de cours', 400);
        }

        $existe = Cours::where('nom', $nom)->first();
        if ($existe) {
            return response()->json(['message' => 'Le cours existe déjà'], 409);
        }

        $cours = $this->coursService->createCours($nom);

        return response()->json($cours, 201);
    }

    public function updateCours(Request $req, $id)
    {
        $nom = $req->input('nom');

        if (! $nom) {
            return response()->json('il faut un nom de cours', 400);
        }
        $existe = Cours::where('nom', $nom)->first();
        if ($existe) {
            return response()->json(['message' => 'Le cours existe déjà'], 409);
        }

        $cours = $this->coursService->updateCours($id, $nom);

        return response()->json($cours, 200);
    }

    public function deleteCours($id)
    {
        $this->coursService->deleteCours($id);

        return response()->json(['message' => 'Cours supprimé avec succès'], 200);
    }
}
