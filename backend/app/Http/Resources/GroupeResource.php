<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupeResource extends JsonResource
{
    // Transforme la resource en un tableau.

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'promotion' => $this->promotion,
            'etudiants' => EtudiantResource::collection($this->whenLoaded('users')),
        ];
    }
}
