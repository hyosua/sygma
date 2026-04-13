<?php

namespace Tests\Feature;

use App\Models\Seance;
use App\Models\SessionEmargement;
use App\Models\User;

class EmargementSecurityTest extends FeatureTestCase
{
    const API_VALIDER_M = '/api/presences/valider-manuel';

    public function test_un_autre_enseignant_ne_peut_pas_valider_presence_manuellement(): void
    {
        // GIVEN
        $enseignantResponsable = User::factory()->create();
        $enseignantResponsable->assignRole('enseignant');

        $autreEnseignant = User::factory()->create();
        $autreEnseignant->assignRole('enseignant');

        $seance = Seance::factory()->create(['enseignant_id' => $enseignantResponsable->id]);
        $session = SessionEmargement::factory()->create(['seance_id' => $seance->id]);
        $etudiant = User::factory()->create(['groupe_id' => $seance->groupe_id]);
        $etudiant->assignRole('etudiant');

        // WHEN
        $response = $this->actingAs($autreEnseignant)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
        ]);

        // THEN
        $response->assertStatus(403);
    }

    public function test_un_etudiant_ne_peut_pas_valider_presence_manuellement(): void
    {
        // GIVEN
        $enseignantResponsable = User::factory()->create();
        $enseignantResponsable->assignRole('enseignant');

        $etudiantFraudeur = User::factory()->create();
        $etudiantFraudeur->assignRole('etudiant');

        $seance = Seance::factory()->create(['enseignant_id' => $enseignantResponsable->id]);
        $session = SessionEmargement::factory()->create(['seance_id' => $seance->id]);
        $etudiant = User::factory()->create(['groupe_id' => $seance->groupe_id]);
        $etudiant->assignRole('etudiant');

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
        $enseignantResponsable = User::factory()->create();
        $enseignantResponsable->assignRole('enseignant');

        $gestionnaire = User::factory()->create();
        $gestionnaire->assignRole('gestionnaire');

        $seance = Seance::factory()->create(['enseignant_id' => $enseignantResponsable->id]);
        $session = SessionEmargement::factory()->create(['seance_id' => $seance->id]);
        $etudiant = User::factory()->create(['groupe_id' => $seance->groupe_id]);
        $etudiant->assignRole('etudiant');

        // WHEN
        $response = $this->actingAs($gestionnaire)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
        ]);

        // THEN
        $response->assertStatus(200);
    }
}
