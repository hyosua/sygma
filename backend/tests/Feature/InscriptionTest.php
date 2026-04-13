<?php

namespace Tests\Feature;

class InscriptionTest extends FeatureTestCase
{
    // POST /register

    public function test_inscription_valide_etudiant_retourne_token(): void
    {
        $response = $this->postJson('/api/register', [
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'email' => 'marie.dupont@test.fr',
            'password' => 'secret123',
            'role' => 'etudiant',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_inscription_valide_enseignant_retourne_token(): void
    {
        $response = $this->postJson('/api/register', [
            'nom' => 'Martin',
            'prenom' => 'Paul',
            'email' => 'paul.martin@test.fr',
            'password' => 'secret123',
            'role' => 'enseignant',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['user', 'token']);
    }

    public function test_email_duplique_retourne_409(): void
    {
        $this->postJson('/api/register', [
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'email' => 'marie.dupont@test.fr',
            'password' => 'secret123',
            'role' => 'etudiant',
        ]);

        $response = $this->postJson('/api/register', [
            'nom' => 'Autre',
            'prenom' => 'Personne',
            'email' => 'marie.dupont@test.fr',
            'password' => 'secret456',
            'role' => 'etudiant',
        ]);

        $response->assertStatus(409);
    }

    public function test_role_gestionnaire_retourne_422(): void
    {
        $response = $this->postJson('/api/register', [
            'nom' => 'Admin',
            'prenom' => 'Test',
            'email' => 'admin@test.fr',
            'password' => 'secret123',
            'role' => 'gestionnaire',
        ]);

        $response->assertStatus(422);
    }

    public function test_role_manquant_retourne_422(): void
    {
        $response = $this->postJson('/api/register', [
            'nom' => 'Admin',
            'prenom' => 'Test',
            'email' => 'admin@test.fr',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422);
    }
}
