<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Seance;

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

        $response = $this->actingAs($enseignant)->post(route('api.emargement.start', [
            'seance' => $session->id,
            'methode' => 'qr',
            ]));

        $response->assertStatus(201);

        $this->assertDatabaseHas('sessions_emargement', [
            'seance_id' => $session->id,
            'methode' => 'qr',
        ]);
    }
}
