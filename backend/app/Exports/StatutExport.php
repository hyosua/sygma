<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StatutExport implements FromQuery, WithHeadings
{
    protected $date;

    protected $statut;

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
                'users.id',
                'users.nom',
                'users.prenom',
                'users.email',
                'cours.nom as cours_nom',
                'seances.debut_a as presence_date'
            )
            ->whereDate('seances.debut_a', $this->date)
            ->where('presences.statut', $this->statut)
            ->orderBy('seances.debut_a');
    }

    public function headings(): array
    {
        return ['id', 'nom', 'prenom', 'email', 'cours', 'date_seance'];
    }
}
