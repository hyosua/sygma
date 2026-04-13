<?php

namespace Tests\Feature;

use App\Models\Seance;
use App\Models\SessionEmargement;
use App\Models\User;

class EmargementSecurityTest extends FeatureTestCase
{
    const API_VALIDER_M = '/api/presences/valider-manuel';

    public function test_un_etudiant_ne_peut_pas_valider_presence_manuellement(): void
    {
        // GIVEN
        $enseignantResponsable = User::factory()->withRole('enseignant')->create();
        $etudiantFraudeur = User::factory()->withRole('etudiant')->create();

        $seance = Seance::factory()->create(['enseignant_id' => $enseignantResponsable->id]);
        $session = SessionEmargement::factory()->create(['seance_id' => $seance->id]);
        $etudiant = User::factory()->withRole('etudiant')->create(['groupe_id' => $seance->groupe_id]);

        // WHEN
        $response = $this->actingAs($etudiantFraudeur)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
        ]);

        // THEN
        $response->assertStatus(403);
    }

    public function test_un_gestionnaire_peut_valider_presence_manuellement(): void
    {
        // GIVEN
        $enseignantResponsable = User::factory()->withRole('enseignant')->create();

        $gestionnaire = User::factory()->withRole('gestionnaire')->create();

        $seance = Seance::factory()->create(['enseignant_id' => $enseignantResponsable->id]);
        $session = SessionEmargement::factory()->create(['seance_id' => $seance->id]);
        $etudiant = User::factory()->withRole('etudiant')->create(['groupe_id' => $seance->groupe_id]);

        // WHEN
        $response = $this->actingAs($gestionnaire)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
        ]);

        // THEN
        $response->assertStatus(200);
    }
}
