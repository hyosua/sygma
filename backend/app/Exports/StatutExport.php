<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StatutExport implements FromQuery, WithHeadings
{
    protected $date, $statut;

    public function __construct($date, $statut)
    {
        $this->date = $date;
        $this->statut = $statut;
    }

    public function query()
    {
        return DB::table('presences')
            ->join('sessions_emargement', 'presences.session_emargement_id', '=', 'sessions_emargement.id')
            ->join('seances', 'sessions_emargement.seance_id', '=', 'seances.id')
            ->join('users', 'presences.etudiant_id', '=', 'users.id')
            ->join('cours', 'seances.cours_id', '=', 'cours.id')
            ->select(
                'users.id', 'users.nom', 'users.prenom', 'users.email',
                'users.created_at as user_created_at', 'users.updated_at as user_updated_at',
                'cours.nom as cours_nom',
                'cours.created_at as cours_created_at', 'cours.updated_at as cours_updated_at',
                'presences.created_at as presence_date'
            )
            ->whereDate('presences.created_at', $this->date)
            ->where('presences.statut', $this->statut);
    }

    public function headings(): array
    {
        return ['id', 'nom', 'prenom', 'email', 'user_created_at', 'user_updated_at', 'cours_nom', 'cours_created_at', 'cours_updated_at', 'presence_date'];
    }
}