<?php

namespace App\Services;

use App\Exceptions\CoursExisteDejaException;
use App\Exceptions\NonAutoriseException;
use App\Models\Cours;
use Illuminate\Support\Facades\Auth;

class CoursService
{
    public function getCours()
    {
        return Cours::all();
    }

    public function createCours(string $nom): Cours
    {
        if (Cours::where('nom', $nom)->exists()) {
            throw new CoursExisteDejaException();
        }

        return Cours::create([
            'nom' => $nom,
            'enseignant_id' => Auth::id(),
        ]);
    }

    public function updateCours(int $id, string $nom): Cours
    {
        $cours = Cours::findOrFail($id);
        if ($cours->enseignant_id !== Auth::id()) {
            throw new NonAutoriseException();
        }

        if (Cours::where('nom', $nom)->where('id', '!=', $id)->exists()) {
            throw new CoursExisteDejaException();
        }

        $cours->nom = $nom;
        $cours->save();

        return $cours;
    }

    public function deleteCours(int $id): void
    {
        $cours = Cours::findOrFail($id);
        if ($cours->enseignant_id !== Auth::id()) {
            throw new NonAutoriseException();
        }

        $cours->delete();
    }
}
