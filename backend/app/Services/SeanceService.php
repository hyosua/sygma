<?php

namespace App\Services;

use App\Exceptions\ConflitSeanceException;
use App\Models\Seance;
use Illuminate\Support\Collection;

class SeanceService
{
    // / Récupère les séances en fonction des filtres fournis
    public function getSeances(array $filtres = []): Collection
    {
        $query = Seance::query();

        if (isset($filtres['enseignant_id'])) {
            $query->where('enseignant_id', $filtres['enseignant_id']);
        }

        if (isset($filtres['groupe_id'])) {
            $query->where('groupe_id', $filtres['groupe_id']);
        }

        if (isset($filtres['cours_id'])) {
            $query->where('cours_id', $filtres['cours_id']);
        }

        if (isset($filtres['date_debut'])) {
            $query->where('debut_a', '>=', $filtres['date_debut']);
        }

        if (isset($filtres['date_fin'])) {
            $query->where('fin_a', '<=', $filtres['date_fin']);
        }

        if (isset($filtres['statut'])) {
            $now = now();
            match ($filtres['statut']) {
                'a_venir' => $query->where('debut_a', '>', $now),
                'en_cours' => $query->where('debut_a', '<=', $now)->where('fin_a', '>=', $now),
                'terminee' => $query->where('fin_a', '<', $now),
                default => null
            };
        }

        return $query->get();
    }

    // Récupère une séance spécifique avec ses relations et le nombre d'inscrits
    public function getSeance(Seance $seance): Seance
    {
        $seance->load(['cours', 'enseignant', 'groupe.users']);
        $seance->nombre_inscrits = $seance->groupe?->users->count() ?? 0;

        return $seance;
    }

    // Créer une séance
    public function creerSeance(array $data): Seance
    {
        $this->verifierConflitSeance($data);

        $seance = Seance::create($data);
        $seance->load(['cours', 'enseignant', 'groupe.users']);

        return $seance;
    }

    // Vérifier si une séance existe déjà dans ce créneau
    private function verifierConflitSeance(array $data): void
    {
        $conflit = Seance::where('enseignant_id', $data['enseignant_id'])
            ->where('debut_a', '<', $data['fin_a'])
            ->where('fin_a', '>', $data['debut_a'])
            ->exists();

        if ($conflit) {
            throw new ConflitSeanceException();
        }
    }
}
