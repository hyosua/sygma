<?php

namespace Tests\Feature;

use App\Models\Seance;
use App\Models\SessionEmargement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_peut_recuperer_la_liste_des_seances(): void
    {
        Seance::factory()->count(3)->create();

        $response = $this->getJson('/api/seances');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_peut_recuperer_une_seance_avec_ses_relations(): void
    {
        $seance = Seance::factory()->create();

        $response = $this->getJson("/api/seances/{$seance->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'debut_a',
                'fin_a',
                'cours' => ['id', 'nom'],
                'enseignant' => ['id', 'nom', 'prenom'],
                'groupe' => ['id', 'nom'],
            ])
            ->assertJsonFragment(['id' => $seance->id]);
    }

    public function test_retourne_404_si_seance_introuvable(): void
    {
        $response = $this->getJson('/api/seances/99999');

        $response->assertStatus(404);
    }

    public function test_liste_seances_vide_retourne_tableau_vide(): void
    {
        $response = $this->getJson('/api/seances');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function test_seance_retourne_les_bons_champs_de_date(): void
    {
        $seance = Seance::factory()->create([
            'debut_a' => '2026-03-10 08:00:00',
            'fin_a' => '2026-03-10 10:00:00',
        ]);

        $response = $this->getJson("/api/seances/{$seance->id}");

        $response->assertStatus(200);

        $this->assertStringContainsString('2026-03-10', $response->json('debut_a'));
        $this->assertStringContainsString('2026-03-10', $response->json('fin_a'));
    }

    public function test_supprime_seance_sans_session_active(): void
    {
        $seance = Seance::factory()->create();

        $response = $this->deleteJson("/api/seances/{$seance->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted($seance); // ou assertDatabaseMissing si hard delete
    }

    public function test_refuse_suppression_si_session_emargement_active(): void
    {
        $seance = Seance::factory()->create();
        SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
            'expire_a' => now()->addMinutes(5),
        ]);

        $response = $this->deleteJson("/api/seances/{$seance->id}");

        $response->assertStatus(422); // ou 409 selon ton handler
    }

    public function test_retourne_404_si_seance_inexistante(): void
    {
        $response = $this->deleteJson('/api/seances/9999');

        $response->assertNotFound();
    }
}
