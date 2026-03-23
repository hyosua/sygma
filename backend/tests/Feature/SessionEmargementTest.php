<?php

namespace Tests\Feature;

use App\Models\Seance;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

class SessionEmargementTest extends FeatureTestCase
{
    use WithFaker;

    const API_URL = '/api/sessions-emargement';

    const API_VALIDER_M = '/api/presences/valider-manuel';

    /**
     * Test pour vérifier que le professeur peut lancer une session d'émargement
     */
    public function test_enseignant_peut_lancer_session_emargement(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);

        $response = $this->actingAs($enseignant)->postJson(self::API_URL, [
            'seance_id' => $seance->id,
            'is_methode_qr' => true,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('sessions_emargement', [
            'seance_id' => $seance->id,
            'is_methode_qr' => true,
        ]);
    }

    public function test_session_emargement_possede_un_jeton_et_une_expiration(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);

        $response = $this->actingAs($enseignant)->postJson(self::API_URL, [
            'seance_id' => $seance->id,
            'is_methode_qr' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'jeton', 'jeton_expire_a', 'seance_id', 'is_methode_qr']);

        $this->assertNotNull($response->json('jeton'));
        $this->assertNotNull($response->json('jeton_expire_a'));
    }

    public function test_enseignant_peut_lancer_session_emargement_sans_qr(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);

        $response = $this->actingAs($enseignant)->postJson(self::API_URL, [
            'seance_id' => $seance->id,
            'is_methode_qr' => false,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('sessions_emargement', [
            'seance_id' => $seance->id,
            'is_methode_qr' => false,
        ]);
    }

    public function test_retourne_erreur_si_seance_inexistante(): void
    {
        $enseignant = User::factory()->enseignant()->create();

        $response = $this->actingAs($enseignant)->postJson(self::API_URL, [
            'seance_id' => 99999,
            'is_methode_qr' => true,
        ]);

        $response->assertStatus(422);
    }

    public function test_peut_cloturer_une_session(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);

        $sessionEmargement = \App\Models\SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
        ]);

        $response = $this->actingAs($enseignant)
            ->postJson("/api/sessions-emargement/{$sessionEmargement->id}/cloturer");

        $response->assertStatus(200);

        $this->assertNotNull($response->json('cloture_a'));
    }

    public function test_statut_ne_rafraichit_pas_jeton_si_session_cloturee(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);

        $sessionEmargement = \App\Models\SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
            'jeton' => 'jeton-initial',
            'jeton_expire_a' => \Carbon\Carbon::now()->subMinute(),
            'cloture_a' => \Carbon\Carbon::now(),
        ]);

        $this->actingAs($enseignant)
            ->getJson("/api/sessions-emargement/{$sessionEmargement->id}/statut");

        $this->assertDatabaseHas('sessions_emargement', [
            'id' => $sessionEmargement->id,
            'jeton' => 'jeton-initial',
        ]);
    }

    public function test_status_retourne_nombre_de_presents(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);

        $sessionEmargement = \App\Models\SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
        ]);

        $etudiants = User::factory()->etudiant()->count(3)->create();
        foreach ($etudiants as $etudiant) {
            \App\Models\Presence::factory()->create([
                'session_emargement_id' => $sessionEmargement->id,
                'etudiant_id' => $etudiant->id,
            ]);
        }

        $response = $this->actingAs($enseignant)
            ->getJson("/api/sessions-emargement/{$sessionEmargement->id}/statut");

        $response->assertStatus(200)
            ->assertJsonFragment(['nombre_presents' => 3]);
    }

    public function test_enseignant_peut_valider_presence_manuellement(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);
        $session = \App\Models\SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
            'is_methode_qr' => false,
        ]);
        $etudiant = User::factory()->etudiant()->create(['groupe_id' => $seance->groupe_id]);

        $response = $this->actingAs($enseignant)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Présence validée avec succès']);

        $this->assertDatabaseHas('presences', [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
            'statut' => 'present',
        ]);
    }

    public function test_statut_retourne_liste_etudiants_avec_statut_presence(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);
        $session = \App\Models\SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
            'is_methode_qr' => false,
        ]);

        $etudiantPresent = User::factory()->etudiant()->create(['groupe_id' => $seance->groupe_id]);
        $etudiantAbsent = User::factory()->etudiant()->create(['groupe_id' => $seance->groupe_id]);

        $this->actingAs($enseignant)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiantPresent->id,
        ]);

        $response = $this->actingAs($enseignant)
            ->getJson("/api/sessions-emargement/{$session->id}/statut");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'etudiant_id' => $etudiantPresent->id,
                'statut' => 'present',
            ])
            ->assertJsonFragment([
                'etudiant_id' => $etudiantAbsent->id,
                'statut' => null,
            ]);
    }

    public function test_retourne_erreur_si_etudiant_non_inscrit_a_la_seance(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);
        $session = \App\Models\SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
            'is_methode_qr' => false,
        ]);
        $etudiantNonInscrit = User::factory()->etudiant()->create(); // groupe_id différent

        $response = $this->actingAs($enseignant)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiantNonInscrit->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_retourne_erreur_si_etudiant_deja_emarge_manuellement(): void
    {
        $enseignant = User::factory()->enseignant()->create();
        $seance = Seance::factory()->create(['enseignant_id' => $enseignant->id]);
        $session = \App\Models\SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
            'is_methode_qr' => false,
        ]);
        $etudiant = User::factory()->etudiant()->create();

        $this->actingAs($enseignant)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
        ]);

        $response = $this->actingAs($enseignant)->postJson(self::API_VALIDER_M, [
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
        ]);

        $response->assertStatus(422);
    }
}
