<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StatutExport implements FromQuery, WithHeadings
{
    public function __construct(
        protected string $dateDebut,
        protected string $dateFin,
        protected ?string $statut = null,
        protected ?string $groupeId = null,
        protected ?string $coursId = null,
        protected ?string $etudiant = null,
    ) {
    }

    public function query()
    {
        $etudiant = $this->etudiant;

        return DB::table('presences')
            ->join('sessions_emargement', 'presences.session_emargement_id', '=', 'sessions_emargement.id')
            ->join('seances', 'sessions_emargement.seance_id', '=', 'seances.id')
            ->join('users', 'presences.etudiant_id', '=', 'users.id')
            ->join('cours', 'seances.cours_id', '=', 'cours.id')
            ->leftJoin('groupes', 'users.groupe_id', '=', 'groupes.id')
            ->select(
                'users.nom',
                'users.prenom',
                'users.email',
                'presences.statut',
                'groupes.nom as groupe',
                'cours.nom as cours_nom',
                'seances.debut_a as date_seance'
            )
            ->when($this->statut, fn ($q) => $q->where('presences.statut', $this->statut))
            ->whereRaw('seances.debut_a::date BETWEEN ? AND ?', [$this->dateDebut, $this->dateFin])
            ->when($this->groupeId, fn ($q) => $q->where('users.groupe_id', $this->groupeId))
            ->when($this->coursId, fn ($q) => $q->where('seances.cours_id', $this->coursId))
            ->when($etudiant, fn ($q) => $q->where(function ($q2) use ($etudiant) {
                $q2->whereRaw('LOWER(users.nom) LIKE ?', ['%' . strtolower($etudiant) . '%'])
                    ->orWhereRaw('LOWER(users.prenom) LIKE ?', ['%' . strtolower($etudiant) . '%']);
            }))
            ->orderBy('seances.debut_a');
    }

    public function headings(): array
    {
        return ['Nom', 'Prénom', 'Email', 'Statut', 'Groupe', 'Cours', 'Date de séance'];
    }
}
