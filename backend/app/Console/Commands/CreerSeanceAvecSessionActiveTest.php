<?php

namespace App\Console\Commands;

use App\Models\Cours;
use App\Models\Groupe;
use App\Models\Seance;
use App\Models\SessionEmargement;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreerSeanceAvecSessionActiveTest extends Command
{
    protected $signature = 'test:seance-session-active
                            {--duree=120 : Durée de la séance en minutes (défaut: 120)}
                            {--expire=10 : Durée de validité du jeton en minutes (défaut: 10)}';

    protected $description = '[DEV] Crée une séance active avec une session d\'émargement active pour tester la suppression bloquée';

    public function handle(): int
    {
        $duree = (int) $this->option('duree');
        $expire = (int) $this->option('expire');

        $enseignant = User::role('Enseignant')->first()
            ?? User::factory()->enseignant()->create();

        $cours = Cours::first() ?? Cours::factory()->create();
        $groupe = Groupe::first() ?? Groupe::factory()->create();

        $seance = Seance::create([
            'cours_id' => $cours->id,
            'enseignant_id' => $enseignant->id,
            'groupe_id' => $groupe->id,
            'debut_a' => now()->subMinutes(5),
            'fin_a' => now()->addMinutes($duree),
        ]);

        $session = SessionEmargement::create([
            'seance_id' => $seance->id,
            'is_methode_qr' => true,
            'jeton' => Str::uuid(),
            'expire_a' => now()->addMinutes($expire),
        ]);

        $this->info("Séance #{$seance->id} créée avec session d'émargement active.");

        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Séance ID',       $seance->id],
                ['Session ID',      $session->id],
                ['Jeton',           $session->jeton],
                ['Session expire à', $session->expire_a->format('H:i:s')],
                ['DELETE (doit échouer)', "DELETE /api/seances/{$seance->id}"],
            ]
        );

        return Command::SUCCESS;
    }
}
