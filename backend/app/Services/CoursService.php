<?php

namespace App\Services;

use App\Exceptions\NonAutoriseException;
use App\Models\Cours;
use Illuminate\Support\Facades\Auth;

class CoursService
{
    public function getCours()
    {
        return Cours::all()->values();
    }

    public function createCours(string $nom): Cours
    {
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
