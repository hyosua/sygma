<?php

namespace Tests\Feature;

use App\Models\Cours;
use App\Models\User;

class CoursControllerTest extends FeatureTestCase
{
    // GET /cours

    public function test_peut_recuperer_la_liste_des_cours(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        Cours::factory()->count(3)->create();

        $response = $this->actingAs($enseignant)->getJson('/api/cours');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_liste_cours_vide_retourne_tableau_vide(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->getJson('/api/cours');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    // POST /Cours/Ajouter

    public function test_peut_creer_un_cours(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->postJson('/api/Cours/Ajouter', [
            'nom' => 'GraphQL',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['nom' => 'GraphQL']);

        $this->assertDatabaseHas('cours', ['nom' => 'GraphQL']);
    }

    public function test_creation_cours_doublon_retourne_409(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        Cours::factory()->create(['nom' => 'SQL']);

        $response = $this->actingAs($enseignant)->postJson('/api/Cours/Ajouter', [
            'nom' => 'SQL',
        ]);

        $response->assertStatus(409);
    }

    // PATCH /Cours/Modifier/{id}

    public function test_peut_modifier_un_cours(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $cours = Cours::factory()->create(['nom' => 'PHP']);

        $response = $this->actingAs($enseignant)->patchJson("/api/Cours/Modifier/{$cours->id}", [
            'nom' => 'PHP avancé',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['nom' => 'PHP avancé']);

        $this->assertDatabaseHas('cours', ['id' => $cours->id, 'nom' => 'PHP avancé']);
    }

    public function test_modification_cours_doublon_retourne_409(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        Cours::factory()->create(['nom' => 'Java']);
        $cours = Cours::factory()->create(['nom' => 'Python']);

        $response = $this->actingAs($enseignant)->patchJson("/api/Cours/Modifier/{$cours->id}", [
            'nom' => 'Java',
        ]);

        $response->assertStatus(409);
    }

    public function test_modification_cours_sans_nom_retourne_400(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $cours = Cours::factory()->create();

        $response = $this->actingAs($enseignant)->patchJson("/api/Cours/Modifier/{$cours->id}", []);

        $response->assertStatus(400);
    }

    public function test_modification_cours_introuvable_retourne_404(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->patchJson('/api/Cours/Modifier/99999', [
            'nom' => 'Test',
        ]);

        $response->assertStatus(404);
    }

    // DELETE /Cours/Supprimer/{id}

    public function test_peut_supprimer_un_cours(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $cours = Cours::factory()->create();

        $response = $this->actingAs($enseignant)->deleteJson("/api/Cours/Supprimer/{$cours->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cours', ['id' => $cours->id]);
    }

    public function test_suppression_cours_introuvable_retourne_404(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->deleteJson('/api/Cours/Supprimer/99999');

        $response->assertStatus(404);
    }
}
