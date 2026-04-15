<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;

class GoogleAuthTest extends FeatureTestCase
{
    private function mockSocialiteUser(string $googleId, string $email, string $nom, string $prenom): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($googleId);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);
        $socialiteUser->shouldReceive('getName')->andReturn("$prenom $nom");
        $socialiteUser->user = ['family_name' => $nom, 'given_name' => $prenom];

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        $socialite = Mockery::mock(SocialiteFactory::class);
        $socialite->shouldReceive('driver')->with('google')->andReturn($provider);

        $this->app->instance(SocialiteFactory::class, $socialite);
    }

    // Cas 1 : google_id connu → connexion directe
    public function test_callback_utilisateur_connu_par_google_id(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123']);
        $user->assignRole('etudiant');

        $this->mockSocialiteUser('google-123', $user->email, $user->nom, $user->prenom);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirectContains('/auth/google/succes?token=');
    }

    // Cas 2 : email connu, google_id null → liaison + connexion
    public function test_callback_email_connu_lie_google_id(): void
    {
        $user = User::factory()->create(['google_id' => null]);
        $user->assignRole('etudiant');

        $this->mockSocialiteUser('google-456', $user->email, $user->nom, $user->prenom);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirectContains('/auth/google/succes?token=');
        $this->assertDatabaseHas('users', ['email' => $user->email, 'google_id' => 'google-456']);
    }

    // Cas 3 : nouvel utilisateur → token temporaire
    public function test_callback_nouvel_utilisateur_retourne_token_temporaire(): void
    {
        $this->mockSocialiteUser('google-789', 'nouveau@test.fr', 'Martin', 'Paul');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirectContains('/inscription/choisir-role?token=');
    }

    // Finalisation valide
    public function test_finaliser_cree_compte_avec_role_valide(): void
    {
        $tokenTemporaire = 'tok-valid-uuid';
        Cache::put('google_temp_' . $tokenTemporaire, [
            'google_id' => 'google-999',
            'email' => 'nouveau@test.fr',
            'nom' => 'Leblanc',
            'prenom' => 'Julie',
        ], now()->addMinutes(5));

        $response = $this->postJson('/api/auth/google/finaliser', [
            'token_temporaire' => $tokenTemporaire,
            'role' => 'etudiant',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['user', 'token']);
        $this->assertDatabaseHas('users', ['email' => 'nouveau@test.fr', 'google_id' => 'google-999']);
        $this->assertNull(Cache::get('google_temp_' . $tokenTemporaire));
    }

    // Finalisation refusée pour le rôle gestionnaire
    public function test_finaliser_refuse_role_gestionnaire(): void
    {
        $tokenTemporaire = 'tok-gestionnaire';
        Cache::put('google_temp_' . $tokenTemporaire, [
            'google_id' => 'google-111',
            'email' => 'gestionnaire@test.fr',
            'nom' => 'Admin',
            'prenom' => 'Super',
        ], now()->addMinutes(5));

        $response = $this->postJson('/api/auth/google/finaliser', [
            'token_temporaire' => $tokenTemporaire,
            'role' => 'gestionnaire',
        ]);

        $response->assertStatus(422);
    }

    // Token temporaire expiré
    public function test_finaliser_token_temporaire_expire(): void
    {
        $response = $this->postJson('/api/auth/google/finaliser', [
            'token_temporaire' => 'token-inexistant',
            'role' => 'etudiant',
        ]);

        $response->assertStatus(422)->assertJsonFragment(['message' => 'Token temporaire invalide ou expiré.']);
    }
}
