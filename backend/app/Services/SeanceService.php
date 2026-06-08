<?php

namespace App\Services;

use App\Exceptions\Seance\ConflitSeanceException;
use App\Exceptions\Seance\SalleOccupeeException;
use App\Exceptions\Seance\SessionEmargementActiveException;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Support\Collection;

class SeanceService
{
    // Récupère les séances en fonction des filtres fournis et du rôle de l'utilisateur
    public function getSeances(array $filtres = [], ?User $utilisateur = null): \Illuminate\Pagination\LengthAwarePaginator|Collection
    {
        $query = Seance::query();

        // Filtre par défaut selon le rôle de l'utilisateur authentifié
        if ($utilisateur && $utilisateur->hasRole('enseignant')) {
            $query->where('enseignant_id', $utilisateur->id);
        } elseif ($utilisateur && $utilisateur->hasRole('etudiant')) {
            $query->where('groupe_id', $utilisateur->groupe_id);
            // Donne moi les séances qui n'ont PAS de session d'émargement correspondant à cette condition
            $query->whereDoesntHave('sessionsEmargement', function ($q) use ($utilisateur) {
                // Une session d'émargement qui a au moins une présence de cet étudiant
                $q->whereHas('presences', fn ($p) => $p->where('etudiant_id', $utilisateur->id));
            });
        } else {
            // gestionnaire : filtres optionnels fournis par le client
            if (isset($filtres['enseignant_id'])) {
                $query->where('enseignant_id', $filtres['enseignant_id']);
            }

            if (isset($filtres['groupe_id'])) {
                $query->where('groupe_id', $filtres['groupe_id']);
            }
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

        $parPage = min((int) ($filtres['par_page'] ?? 15), 50);

        return $query->withCount('etudiants as nombre_inscrits')->with(['cours', 'enseignant', 'groupe'])->orderBy('debut_a', 'asc')->paginate($parPage);
    }

    // Récupère une séance spécifique avec ses relations et le nombre d'inscrits
    public function getSeance(Seance $seance): Seance
    {
        $seance->load(['cours', 'enseignant', 'groupe']);
        $seance->loadCount('etudiants as nombre_inscrits');

        return $seance;
    }

    // Récupère les sessions d'une séance donnée
    public function getSessions(Seance $seance): Collection
    {
        return $seance->sessionsEmargement;
    }

    // Créer une séance
    public function creerSeance(array $data): Seance
    {
        $this->verifierConflitSeance($data);
        if (isset($data['salle'])) {
            $this->verifierConflitSalle($data);
        }

        $seance = Seance::create($data);
        $seance->load(['cours', 'enseignant', 'groupe']);
        $seance->loadCount('etudiants as nombre_inscrits');

        return $seance;
    }

    // Supprimer une séance
    public function supprimerSeance(Seance $seance): void
    {
        $this->verifierSessionEmargementActive($seance);
        $seance->delete();
    }

    // Modifier une séance
    public function modifierSeance(Seance $seance, array $data): Seance
    {
        $this->verifierSessionEmargementActive($seance);
        $this->verifierConflitSeance($data, $seance->id);
        if (isset($data['salle'])) {
            $this->verifierConflitSalle($data, $seance->id);
        }

        $seance->update($data);
        $seance->load(['cours', 'enseignant', 'groupe']);
        $seance->loadCount('etudiants as nombre_inscrits');

        return $seance;
    }

    // Vérifier si une séance existe déjà dans ce créneau
    private function verifierConflitSeance(array $data, ?int $selfId = null): void
    {
        $requete = Seance::where('enseignant_id', $data['enseignant_id'])
            ->where('debut_a', '<', $data['fin_a'])
            ->where('fin_a', '>', $data['debut_a']);

        // Ne pas inclure sa propre séance si l'on souhaite la modifier
        if ($selfId) {
            $requete->where('id', '!=', $selfId);
        }

        if ($requete->exists()) {
            throw new ConflitSeanceException();
        }
    }

    private function verifierConflitSalle(array $data, ?int $selfId = null): void
    {

        $requete = Seance::where('salle', $data['salle'])
            ->where('debut_a', '<', $data['fin_a'])
            ->where('fin_a', '>', $data['debut_a']);

        if ($selfId) {
            $requete->where('id', '!=', $selfId);
        }

        if ($requete->exists()) {
            throw new SalleOccupeeException();
        }
    }

    private function verifierSessionEmargementActive(Seance $seance): void
    {
        $sessionEmargementActive = $seance->sessionsEmargement()->whereNull('cloture_a')->exists();

        if ($sessionEmargementActive) {
            throw new SessionEmargementActiveException();
        }
    }
}
