<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\Groupe;
use App\Models\Inscription;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\SessionEmargement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents; // Pour éviter les événements lors de la création des modèles

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Créer les rôles
        $this->command->info('Création des rôles et permissions...');
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Créer l'utilisateur gestionnaire
        $this->command->info('Création du gestionnaire...');
        User::factory()->withRole('gestionnaire')->create([
            'nom' => 'Admin',
            'prenom' => 'Sygma',
            'email' => 'admin@sygma.com',
            'password' => Hash::make('password'),
            'premiere_connexion' => false,
        ]);

        // 3. Créer les enseignants
        $this->command->info('Création des enseignants...');
        $enseignants = User::factory(5)->enseignant()->create();

        // 4. Créer les cours
        $this->command->info('Création des cours...');
        $cours = Cours::factory(10)->create();

        // 5. Créer les groupes avec leurs étudiants et leurs inscriptions
        $this->command->info('Création des groupes, étudiants et inscriptions...');
        Groupe::factory(3)
            ->create()
            ->each(function (Groupe $groupe) use ($cours, $enseignants) {
                $this->command->getOutput()->writeln("  <info>Groupe : {$groupe->libelle}</info>");

                // Pour chaque groupe, créer 20 étudiants
                $etudiants = User::factory(20)
                    ->etudiant()
                    ->create(['groupe_id' => $groupe->id]);
                $this->command->getOutput()->writeln("    <comment>-> 20 étudiants créés.</comment>");

                // Inscrire le groupe à 4 cours au hasard
                $coursPourLeGroupe = $cours->random(4);
                foreach ($etudiants as $etudiant) {
                    foreach ($coursPourLeGroupe as $c) {
                        Inscription::factory()->create([
                            'utilisateur_id' => $etudiant->id,
                            'cours_id' => $c->id,
                        ]);
                    }
                }
                $this->command->getOutput()->writeln("    <comment>-> Inscription des étudiants à 4 cours.</comment>");

                // Pour chaque cours du groupe, créer 5 séances
                foreach ($coursPourLeGroupe as $c) {
                    $this->command->getOutput()->writeln("    <info>  Cours : {$c->libelle}</info>");
                    $seances = Seance::factory(5)->create([
                        'cours_id' => $c->id,
                        'groupe_id' => $groupe->id,
                        'enseignant_id' => $enseignants->random()->id,
                    ]);
                    $this->command->getOutput()->writeln("      <comment>-> 5 séances créées.</comment>");

                    // Pour les 3 premières séances (passées), créer l'émargement
                    foreach ($seances->take(3) as $seance) {
                        $session = SessionEmargement::factory()->create([
                            'seance_id' => $seance->id,
                        ]);

                        // Enregistrer la présence de chaque étudiant
                        foreach ($etudiants as $etudiant) {
                            Presence::factory()->create([
                                'session_emargement_id' => $session->id,
                                'etudiant_id' => $etudiant->id,
                            ]);
                        }
                    }
                    $this->command->getOutput()->writeln("      <comment>-> Émargement créé pour 3 séances.</comment>");
                }
            });

        $this->command->info('Terminé !');
    }
}
