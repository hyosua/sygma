<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\Groupe;
use App\Models\Inscription;
use App\Models\InvitationGestionnaire;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\SessionEmargement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /*
     * Comptes de test disponibles après make fresh :
     *
     *  admin@sygma.com      / sygma  → gestionnaire (voit tout)
     *  enseignant@sygma.com / sygma  → Jean Dupont (voit ses séances du groupeDemo)
     *  etudiant@sygma.com   / sygma  → Alice Martin (groupeDemo, peut valider en live dans la démo)
     *  etudiant2@sygma.com  / sygma  → Bob Leroy   (groupeDemo, peut valider en live dans la démo)
     *
     * Ce que voit enseignant@sygma.com :
     *   - 4 cours × 3 séances passées (avec émargement clôturé + présences)
     *   - 4 cours × 2 séances à venir
     *   - 1 séance de démo en cours avec session ouverte (0 présence enregistrée)
     *
     * Ce que voient etudiant@sygma.com et etudiant2@sygma.com :
     *   - Les mêmes séances que leur groupe (groupeDemo)
     *   - Présences enregistrées sur les séances passées
     */
    public function run(): void
    {
        // 1. Rôles
        $this->command->info('Création des rôles...');
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Gestionnaire
        $this->command->info('Création du gestionnaire...');
        User::firstOrCreate(['email' => 'admin@sygma.com'], [
            'nom' => 'Admin',
            'prenom' => 'Sygma',
            'password' => Hash::make('sygma'),
            'premiere_connexion' => false,
        ])->assignRole('gestionnaire');

        // Invitations gestionnaire
        $this->command->info('Création des invitations gestionnaire...');
        InvitationGestionnaire::firstOrCreate(['email' => 'invite@sygma.com'], [
            'token' => 'abc123token',
            'expires_at' => now()->addDays(7),
        ]);
        InvitationGestionnaire::firstOrCreate(['email' => 'expired@sygma.com'], [
            'token' => 'expiredtoken',
            'expires_at' => now()->subDays(1),
        ]);
        InvitationGestionnaire::firstOrCreate(['email' => 'used@sygma.com'], [
            'token' => 'usedtoken',
            'expires_at' => now()->addDays(7),
            'used_at' => now(),
        ]);

        // 3. Enseignants — enseignant@sygma.com est exclu du pool aléatoire
        $this->command->info('Création des enseignants...');
        $enseignantFixe = User::firstOrCreate(['email' => 'enseignant@sygma.com'], [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'password' => Hash::make('sygma'),
            'premiere_connexion' => false,
        ]);
        $enseignantFixe->assignRole('enseignant');

        $autresEnseignants = User::factory(4)->enseignant()->create();
        $tousLesEnseignants = collect([$enseignantFixe])->concat($autresEnseignants);

        // 4. Cours
        $this->command->info('Création des cours...');
        $cours = Cours::factory(10)
            ->sequence(fn () => ['enseignant_id' => $tousLesEnseignants->random()->id])
            ->create();

        // Créneaux horaires : 4 plages fixes (une par cours du groupe)
        $creneaux = [
            ['heure_debut' => 8,  'heure_fin' => 10],
            ['heure_debut' => 10, 'heure_fin' => 12],
            ['heure_debut' => 14, 'heure_fin' => 16],
            ['heure_debut' => 16, 'heure_fin' => 18],
        ];

        // 5. Groupes, étudiants, inscriptions, séances
        $this->command->info('Création des groupes, étudiants et séances...');
        $groupeDemo = null;
        $estPremierGroupe = true;

        Groupe::factory(3)->create()->each(
            function (Groupe $groupe) use (
                $cours,
                $enseignantFixe,
                $autresEnseignants,
                $creneaux,
                &$estPremierGroupe,
                &$groupeDemo
            ) {
                $this->command->getOutput()->writeln("  <info>Groupe : {$groupe->libelle}</info>");

                // Créer 20 étudiants pour ce groupe
                $etudiants = User::factory(20)->etudiant()->create(['groupe_id' => $groupe->id]);

                // Dans le premier groupe : ajouter etudiant@sygma.com et etudiant2@sygma.com
                if ($estPremierGroupe) {
                    $groupeDemo = $groupe;

                    $etudiantFixe = User::firstOrCreate(['email' => 'etudiant@sygma.com'], [
                        'nom' => 'Martin',
                        'prenom' => 'Alice',
                        'password' => Hash::make('sygma'),
                        'premiere_connexion' => false,
                        'groupe_id' => $groupe->id,
                    ]);
                    $etudiantFixe->assignRole('etudiant');

                    $etudiantFixe2 = User::firstOrCreate(['email' => 'etudiant2@sygma.com'], [
                        'nom' => 'Leroy',
                        'prenom' => 'Bob',
                        'password' => Hash::make('sygma'),
                        'premiere_connexion' => false,
                        'groupe_id' => $groupe->id,
                    ]);
                    $etudiantFixe2->assignRole('etudiant');

                    $etudiants = $etudiants->prepend($etudiantFixe2)->prepend($etudiantFixe);
                    $estPremierGroupe = false;
                }

                $this->command->getOutput()->writeln('    <comment>-> ' . $etudiants->count() . ' étudiants créés.</comment>');

                // 4 cours aléatoires pour ce groupe
                $coursPourLeGroupe = $cours->random(4);

                // Inscrire tous les étudiants à ces 4 cours
                foreach ($etudiants as $etudiant) {
                    foreach ($coursPourLeGroupe as $c) {
                        Inscription::factory()->create([
                            'utilisateur_id' => $etudiant->id,
                            'cours_id' => $c->id,
                        ]);
                    }
                }
                $this->command->getOutput()->writeln('    <comment>-> Inscriptions aux 4 cours créées.</comment>');

                // Pour le groupeDemo : enseignantFixe enseigne tous les cours
                // Pour les autres groupes : enseignants aléatoires (jamais enseignantFixe)
                $estGroupeDemo = ($groupe->id === $groupeDemo?->id);

                foreach ($coursPourLeGroupe->values() as $indexCours => $c) {
                    $enseignant = $estGroupeDemo
                        ? $enseignantFixe
                        : $autresEnseignants->random();

                    $creneau = $creneaux[$indexCours];

                    // 3 séances passées (semaines -4, -3, -2)
                    $seancesPassees = collect();
                    foreach ([4, 3, 2] as $semainesAgo) {
                        $debut = now()->subWeeks($semainesAgo)->setTime($creneau['heure_debut'], 0);
                        $fin = now()->subWeeks($semainesAgo)->setTime($creneau['heure_fin'], 0);

                        $seancesPassees->push(Seance::factory()->create([
                            'cours_id' => $c->id,
                            'groupe_id' => $groupe->id,
                            'enseignant_id' => $enseignant->id,
                            'debut_a' => $debut,
                            'fin_a' => $fin,
                        ]));
                    }

                    // Émargement clôturé + présences pour chaque séance passée
                    foreach ($seancesPassees as $seance) {
                        $session = SessionEmargement::factory()->create([
                            'seance_id' => $seance->id,
                            'cloture_a' => $seance->fin_a,
                            'jeton_expire_a' => $seance->fin_a,
                        ]);

                        foreach ($etudiants as $etudiant) {
                            Presence::factory()->create([
                                'session_emargement_id' => $session->id,
                                'etudiant_id' => $etudiant->id,
                            ]);
                        }
                    }

                    // 2 séances à venir (semaines +1, +2)
                    foreach ([1, 2] as $semainesFuture) {
                        $debut = now()->addWeeks($semainesFuture)->setTime($creneau['heure_debut'], 0);
                        $fin = now()->addWeeks($semainesFuture)->setTime($creneau['heure_fin'], 0);

                        Seance::factory()->create([
                            'cours_id' => $c->id,
                            'groupe_id' => $groupe->id,
                            'enseignant_id' => $enseignant->id,
                            'debut_a' => $debut,
                            'fin_a' => $fin,
                        ]);
                    }

                    $this->command->getOutput()->writeln(
                        "      <comment>-> Cours {$c->libelle} : 3 séances passées + 2 à venir.</comment>"
                    );
                }
            }
        );

        // 6. Séances actives avec session en cours (autres enseignants uniquement)
        $this->command->info('Création des séances actives avec sessions d\'émargement en cours...');
        $groupes = Groupe::where('id', '!=', $groupeDemo->id)->get();
        $nbSessions = 0;

        foreach ($groupes as $groupe) {
            if ($nbSessions >= 5) {
                break;
            }
            foreach ($cours->random(2) as $c) {
                if ($nbSessions >= 5) {
                    break;
                }
                $seanceActive = Seance::factory()->create([
                    'cours_id' => $c->id,
                    'groupe_id' => $groupe->id,
                    'enseignant_id' => $autresEnseignants->random()->id,
                    'debut_a' => now()->subMinutes(30),
                    'fin_a' => now()->addMinutes(90),
                ]);

                SessionEmargement::factory()->create([
                    'seance_id' => $seanceActive->id,
                    'jeton_expire_a' => now()->addSeconds(20),
                ]);

                $nbSessions++;
                $this->command->getOutput()->writeln(
                    "  <comment>-> Session active #{$nbSessions} (groupe : {$groupe->libelle}, cours : {$c->libelle}).</comment>"
                );
            }
        }

        // 7. Séance de démo pour enseignant@sygma.com (en cours, sans session démarrée)
        // → même groupe qu'etudiant@sygma.com, cours auquel il est inscrit
        $this->command->info('Création de la séance de démo pour enseignant@sygma.com...');
        $coursDemo = Cours::whereHas('inscriptions', function ($q) use ($groupeDemo) {
            $q->whereHas('utilisateur', fn ($u) => $u->where('groupe_id', $groupeDemo->id));
        })->inRandomOrder()->first() ?? $cours->first();

        $seanceDemo = Seance::factory()->create([
            'cours_id' => $coursDemo->id,
            'groupe_id' => $groupeDemo->id,
            'enseignant_id' => $enseignantFixe->id,
            'debut_a' => now()->subMinutes(30),
            'fin_a' => now()->addMinutes(90),
        ]);

        SessionEmargement::factory()->create([
            'seance_id' => $seanceDemo->id,
            'jeton_expire_a' => now()->addSeconds(20),
        ]);

        $this->command->info('-> Séance de démo créée (en cours, avec session ouverte - aucune présence pré-enregistrée).');

        $this->command->info('');
        $this->command->info('✓ Comptes de test :');
        $this->command->info('  admin@sygma.com      / sygma  → gestionnaire');
        $this->command->info('  enseignant@sygma.com / sygma  → Jean Dupont (séances groupeDemo)');
        $this->command->info('  etudiant@sygma.com   / sygma  → Alice Martin (groupeDemo, valide en live)');
        $this->command->info('  etudiant2@sygma.com  / sygma  → Bob Leroy   (groupeDemo, valide en live)');
    }
}
