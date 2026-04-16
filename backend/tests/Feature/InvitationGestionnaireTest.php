<?php

namespace Tests\Feature;

use App\Mail\InvitationGestionnaireEmail;
use App\Models\InvitationGestionnaire;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationGestionnaireTest extends FeatureTestCase
{
    private function creerGestionnaire(): User
    {
        $gestionnaire = User::factory()->create();
        $gestionnaire->assignRole('gestionnaire');

        return $gestionnaire;
    }

    // POST /gestionnaire/invitations

    public function test_gestionnaire_peut_inviter_un_email(): void
    {
        Mail::fake();

        $gestionnaire = $this->creerGestionnaire();

        $response = $this->actingAs($gestionnaire)->postJson('/api/gestionnaire/invitations', [
            'email' => 'nouveau@test.fr',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('invitations_gestionnaire', ['email' => 'nouveau@test.fr']);
        Mail::assertSent(InvitationGestionnaireEmail::class, fn ($mail) => $mail->hasTo('nouveau@test.fr'));
    }

    public function test_inviter_sans_email_retourne_422(): void
    {
        $gestionnaire = $this->creerGestionnaire();

        $response = $this->actingAs($gestionnaire)->postJson('/api/gestionnaire/invitations', []);

        $response->assertStatus(422);
    }

    public function test_inviter_email_invalide_retourne_422(): void
    {
        $gestionnaire = $this->creerGestionnaire();

        $response = $this->actingAs($gestionnaire)->postJson('/api/gestionnaire/invitations', [
            'email' => 'pas-un-email',
        ]);

        $response->assertStatus(422);
    }

    public function test_non_authentifie_ne_peut_pas_inviter(): void
    {
        $response = $this->postJson('/api/gestionnaire/invitations', [
            'email' => 'nouveau@test.fr',
        ]);

        $response->assertStatus(401);
    }

    public function test_etudiant_ne_peut_pas_inviter(): void
    {
        $etudiant = User::factory()->create();
        $etudiant->assignRole('etudiant');

        $response = $this->actingAs($etudiant)->postJson('/api/gestionnaire/invitations', [
            'email' => 'nouveau@test.fr',
        ]);

        $response->assertStatus(403);
    }

    public function test_reinviter_meme_email_remplace_invitation(): void
    {
        Mail::fake();

        $gestionnaire = $this->creerGestionnaire();

        $this->actingAs($gestionnaire)->postJson('/api/gestionnaire/invitations', [
            'email' => 'nouveau@test.fr',
        ]);

        $this->actingAs($gestionnaire)->postJson('/api/gestionnaire/invitations', [
            'email' => 'nouveau@test.fr',
        ]);

        $this->assertDatabaseCount('invitations_gestionnaire', 1);
    }

    // GET /gestionnaire/invitations

    public function test_gestionnaire_peut_lister_invitations(): void
    {
        $gestionnaire = $this->creerGestionnaire();

        InvitationGestionnaire::create([
            'email' => 'a@test.fr',
            'token' => Str::random(32),
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->actingAs($gestionnaire)->getJson('/api/gestionnaire/invitations');

        $response->assertStatus(200)->assertJsonCount(1);
    }

    // DELETE /gestionnaire/invitations/{invitation}

    public function test_gestionnaire_peut_annuler_invitation(): void
    {
        $gestionnaire = $this->creerGestionnaire();

        $invitation = InvitationGestionnaire::create([
            'email' => 'a@test.fr',
            'token' => Str::random(32),
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->actingAs($gestionnaire)->deleteJson("/api/gestionnaire/invitations/{$invitation->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('invitations_gestionnaire', ['id' => $invitation->id]);
    }

    // GET /invitations/gestionnaire/{token}

    public function test_token_valide_retourne_invitation(): void
    {
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'a@test.fr',
            'token' => $token,
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->getJson("/api/invitations/gestionnaire/{$token}");

        $response->assertStatus(200)->assertJsonFragment(['email' => 'a@test.fr']);
    }

    public function test_token_inexistant_retourne_erreur(): void
    {
        $response = $this->getJson('/api/invitations/gestionnaire/token-inexistant');

        $response->assertStatus(422);
    }

    public function test_token_expire_retourne_erreur(): void
    {
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'a@test.fr',
            'token' => $token,
            'expires_at' => now()->subHours(1),
        ]);

        $response = $this->getJson("/api/invitations/gestionnaire/{$token}");

        $response->assertStatus(422);
    }

    public function test_token_deja_utilise_retourne_erreur(): void
    {
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'a@test.fr',
            'token' => $token,
            'expires_at' => now()->addHours(48),
            'used_at' => now(),
        ]);

        $response = $this->getJson("/api/invitations/gestionnaire/{$token}");

        $response->assertStatus(422);
    }

    // POST /invitations/gestionnaire/{token}

    public function test_inscription_via_token_valide_cree_gestionnaire(): void
    {
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'nouveau@test.fr',
            'token' => $token,
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->postJson("/api/invitations/gestionnaire/{$token}", [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'password' => 'motdepasse123',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['message', 'token']);

        $user = User::where('email', 'nouveau@test.fr')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('gestionnaire'));

        $invitation = InvitationGestionnaire::where('token', $token)->first();
        $this->assertNotNull($invitation->used_at);
    }

    public function test_inscription_sans_nom_retourne_422(): void
    {
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'nouveau@test.fr',
            'token' => $token,
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->postJson("/api/invitations/gestionnaire/{$token}", [
            'password' => 'motdepasse123',
        ]);

        $response->assertStatus(422);
    }

    public function test_inscription_mot_de_passe_trop_court_retourne_422(): void
    {
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'nouveau@test.fr',
            'token' => $token,
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->postJson("/api/invitations/gestionnaire/{$token}", [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'password' => 'court',
        ]);

        $response->assertStatus(422);
    }

    public function test_inscription_token_expire_retourne_erreur(): void
    {
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'nouveau@test.fr',
            'token' => $token,
            'expires_at' => now()->subHours(1),
        ]);

        $response = $this->postJson("/api/invitations/gestionnaire/{$token}", [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'password' => 'motdepasse123',
        ]);

        $response->assertStatus(422);
    }

    public function test_inscription_token_deja_utilise_retourne_erreur(): void
    {
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'nouveau@test.fr',
            'token' => $token,
            'expires_at' => now()->addHours(48),
            'used_at' => now(),
        ]);

        $response = $this->postJson("/api/invitations/gestionnaire/{$token}", [
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'password' => 'motdepasse123',
        ]);

        $response->assertStatus(422);
    }

    // POST /gestionnaire/invitations/{token}/renvoyer

    public function test_gestionnaire_peut_renvoyer_invitation(): void
    {
        Mail::fake();

        $gestionnaire = $this->creerGestionnaire();
        $token = Str::random(32);

        InvitationGestionnaire::create([
            'email' => 'a@test.fr',
            'token' => $token,
            'expires_at' => now()->addHours(48),
        ]);

        $response = $this->actingAs($gestionnaire)->postJson("/api/gestionnaire/invitations/{$token}/renvoyer");

        $response->assertStatus(200)->assertJsonFragment(['message' => 'Invitation renvoyée.']);
        Mail::assertSent(InvitationGestionnaireEmail::class, fn ($mail) => $mail->hasTo('a@test.fr'));
    }

    public function test_renvoyer_token_inexistant_retourne_erreur(): void
    {
        $gestionnaire = $this->creerGestionnaire();

        $response = $this->actingAs($gestionnaire)->postJson('/api/gestionnaire/invitations/token-inexistant/renvoyer');

        $response->assertStatus(403);
    }
}
