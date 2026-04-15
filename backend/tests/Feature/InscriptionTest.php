<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InscriptionTest extends FeatureTestCase
{
    // POST /register

    public function test_inscription_valide_etudiant_envoie_email_confirmation(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/register', [
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'email' => 'marie.dupont@test.fr',
            'password' => 'secret123',
            'role' => 'etudiant',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message'])
            ->assertJsonMissing(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'marie.dupont@test.fr',
        ]);

        $user = User::where('email', 'marie.dupont@test.fr')->first();
        $this->assertNotNull($user->verification_token);
        $this->assertNull($user->email_verified_at);

        Mail::assertSent(\App\Mail\ConfirmationEmail::class, fn ($mail) => $mail->hasTo('marie.dupont@test.fr'));
    }

    public function test_inscription_valide_enseignant_envoie_email_confirmation(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/register', [
            'nom' => 'Martin',
            'prenom' => 'Paul',
            'email' => 'paul.martin@test.fr',
            'password' => 'secret123',
            'role' => 'enseignant',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message']);

        Mail::assertSent(\App\Mail\ConfirmationEmail::class);
    }

    public function test_email_duplique_retourne_422(): void
    {
        Mail::fake();

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

        $response->assertStatus(422);
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

    // GET /email/verify/{token}

    public function test_token_valide_retourne_token_sanctum(): void
    {
        $token = Str::uuid()->toString();

        $user = User::factory()->create([
            'email_verified_at' => null,
            'verification_token' => $token,
            'verification_token_expires_at' => now()->addHours(24),
        ]);
        $user->assignRole('etudiant');

        $response = $this->getJson("/api/email/verify/{$token}");

        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNull($user->fresh()->verification_token);
    }

    public function test_token_expire_retourne_erreur(): void
    {
        $token = Str::uuid()->toString();

        $user = User::factory()->create([
            'email_verified_at' => null,
            'verification_token' => $token,
            'verification_token_expires_at' => now()->subHours(1),
        ]);
        $user->assignRole('etudiant');

        $response = $this->getJson("/api/email/verify/{$token}");

        $response->assertStatus(410);
    }

    public function test_token_invalide_retourne_erreur(): void
    {
        $response = $this->getJson('/api/email/verify/token-inexistant');

        $response->assertStatus(404);
    }
}
