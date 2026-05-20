<?php

namespace App\Http\Controllers;

use App\Http\Resources\EtudiantResource;
use App\Http\Resources\GroupeResource;
use App\Models\Groupe;
use App\Models\User;
use Illuminate\Http\Request;

class GroupeController extends Controller
{
    public function index()
    {
        return response()->json(GroupeResource::collection(Groupe::all()));
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'nom' => 'required|string|max:255|unique:groupes,nom',
            'promotion' => 'nullable|string|max:255',
        ]);

        $groupe = Groupe::create($donnees);

        return response()->json(new GroupeResource($groupe), 201);
    }

    public function update(Request $request, Groupe $groupe)
    {
        $donnees = $request->validate([
            'nom' => 'sometimes|required|string|max:255|unique:groupes,nom,' . $groupe->id,
            'promotion' => 'nullable|string|max:255',
        ]);

        $groupe->update($donnees);

        return new GroupeResource($groupe);
    }

    public function destroy(Groupe $groupe)
    {
        $groupe->delete();

        return response()->json(null, 204);
    }

    public function etudiants($id)
    {
        return EtudiantResource::collection(
            User::where('groupe_id', $id)->get()
        );
    }
}
