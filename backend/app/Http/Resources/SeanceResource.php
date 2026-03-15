<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeanceResource extends JsonResource
{
    // Transforme la resource en un tableau.

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'debut_a' => $this->debut_a,
            'fin_a' => $this->fin_a,
            'salle' => $this->salle,
            'statut' => $this->statut,
            'nombre_inscrits' => $this->nombre_inscrits,
            'cours' => new CoursResource($this->cours),
            'enseignant' => new EnseignantResource($this->enseignant),
            'groupe' => new GroupeResource($this->groupe),
        ];
    }
}
