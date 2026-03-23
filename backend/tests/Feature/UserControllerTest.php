<?php

namespace Tests\Feature;

use App\Models\User;

class UserControllerTest extends FeatureTestCase
{
    // GET /user/{user}

    public function test_peut_recuperer_un_utilisateur(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $cible = User::factory()->create();

        $response = $this->actingAs($enseignant)->getJson("/api/user/{$cible->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $cible->id, 'email' => $cible->email]);
    }

    public function test_utilisateur_introuvable_retourne_404(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->getJson('/api/user/99999');

        $response->assertStatus(404);
    }

    // POST /users

    public function test_peut_creer_un_utilisateur(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->postJson('/api/users', [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont@test.fr',
            'password' => 'secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['email' => 'jean.dupont@test.fr']);

        $this->assertDatabaseHas('users', ['email' => 'jean.dupont@test.fr']);
    }

    public function test_creation_utilisateur_doublon_email_retourne_409(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        User::factory()->create(['email' => 'existant@test.fr']);

        $response = $this->actingAs($enseignant)->postJson('/api/users', [
            'nom' => 'Martin',
            'prenom' => 'Paul',
            'email' => 'existant@test.fr',
            'password' => 'secret123',
        ]);

        $response->assertStatus(409);
    }

    // PATCH /users/{user}

    public function test_peut_modifier_un_utilisateur(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $cible = User::factory()->create(['nom' => 'Ancien']);

        $response = $this->actingAs($enseignant)->patchJson("/api/users/{$cible->id}", [
            'nom' => 'Nouveau',
            'prenom' => $cible->prenom,
            'email' => $cible->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['nom' => 'Nouveau']);

        $this->assertDatabaseHas('users', ['id' => $cible->id, 'nom' => 'Nouveau']);
    }

    public function test_modification_utilisateur_introuvable_retourne_404(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->patchJson('/api/users/99999', [
            'nom' => 'Test',
        ]);

        $response->assertStatus(404);
    }

    // DELETE /users/{user}

    public function test_peut_supprimer_un_utilisateur(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $cible = User::factory()->create();

        $response = $this->actingAs($enseignant)->deleteJson("/api/users/{$cible->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $cible->id]);
    }

    public function test_suppression_utilisateur_introuvable_retourne_404(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->deleteJson('/api/users/99999');

        $response->assertStatus(404);
    }
}
