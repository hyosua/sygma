<?php

namespace Database\Seeders;

use App\Models\Groupe;
use App\Models\User;
use Illuminate\Database\Seeder;

class GroupeSeeder extends Seeder
{
    public function run(): void
    {
        $dawii = Groupe::create([
            'nom' => 'LP Dawii',
            'promotion' => '2025-2026'
        ]);

        $asri = Groupe::create([
            'nom' => 'LP ASRI',
            'promotion' => '2025-2026'
        ]);

        // Création d'un étudiant de test
        $etudiant = User::create([
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'ine' => '1234567890X',
            'groupe_id' => $dawii->id,
            'premiere_connexion' => true,
        ]);
        $etudiant->assignRole('Etudiant');

        $etudiant = User::create([
            'nom' => 'Roegan',
            'prenom' => 'John',
            'email' => 'student2@test.com',
            'password' => bcrypt('password'),
            'ine' => '1234567890Y',
            'groupe_id' => $asri->id,
            'premiere_connexion' => true,
        ]);
        $etudiant->assignRole('Etudiant');

        // Création d'un enseignant de test
        $prof = User::create([
            'nom' => 'Smith',
            'prenom' => 'Jane',
            'email' => 'teacher@test.com',
            'password' => bcrypt('password'),
            'specialites' => ['PHP', 'Laravel', 'Architecture'],
            'premiere_connexion' => false,
        ]);
        $prof->assignRole('Professeur');
    }
}
