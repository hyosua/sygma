<?php

namespace App\Console\Commands;

use App\Models\Cours;
use App\Models\Groupe;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Console\Command;

class CreerSeanceActiveTest extends Command
{
    protected $signature = 'test:seance-active
                            {--duree=120 : Durée de la séance en minutes (défaut: 120)}
                            {--reset : Remet à jour une séance existante plutôt qu\'en créer une nouvelle}';

    protected $description = '[DEV] Crée ou remet à jour une séance active pour tester l\'émargement';

    public function handle(): int
    {
        $duree = (int) $this->option('duree');
        $debut = now()->subMinutes(5);
        $fin = now()->addMinutes($duree);

        if ($this->option('reset')) {
            $seance = Seance::latest()->first();

            if (! $seance) {
                $this->error('Aucune séance en base. Relancez sans --reset.');

                return Command::FAILURE;
            }

            $seance->update(['debut_a' => $debut, 'fin_a' => $fin]);
            $this->info("Séance #{$seance->id} mise à jour.");
        } else {
            $enseignant = User::role('Enseignant')->first()
                ?? User::factory()->enseignant()->create();

            $cours = Cours::first() ?? Cours::factory()->create();
            $groupe = Groupe::first() ?? Groupe::factory()->create();

            $seance = Seance::create([
                'cours_id' => $cours->id,
                'enseignant_id' => $enseignant->id,
                'groupe_id' => $groupe->id,
                'debut_a' => $debut,
                'fin_a' => $fin,
            ]);

            $this->info("Séance #{$seance->id} créée.");
        }

        $this->table(
            ['Champ', 'Valeur'],
            [
                ['ID',        $seance->id],
                ['Cours',     $seance->cours->libelle ?? $seance->cours_id],
                ['Début',     $seance->debut_a->format('H:i')],
                ['Fin',       $seance->fin_a->format('H:i')],
                ['Active ?',  $seance->isActive() ? 'OUI' : 'NON'],
                ['URL',       "/enseignant/session/{$seance->id}"],
            ]
        );

        return Command::SUCCESS;
    }
}
