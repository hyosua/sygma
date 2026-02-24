<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SessionEmargementTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    /**
     * Test pour vérifier que le professeur peut lancer une session d'émargement
     */
    public function test_enseignant_peut_lancer_session_emargement(): void
    {
        // Création d'un enseignant
        $enseignant = User::factory()->enseignant()->create();
        // Création d'une session de cours pour cet enseignant
        $session = Seance::factory()->create(['enseignant_id' => $enseignant->id]);

        $response = $this->actingAs($enseignant)->get(route('session.emargement', ['seance' => $session]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('seances', [
            'id' => $session->id,
            'enseignant_id' => $enseignant->id,
        ]);
    }
}
