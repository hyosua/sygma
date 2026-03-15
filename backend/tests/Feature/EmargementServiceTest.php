<?php

namespace Tests\Feature;

use App\Exceptions\Emargement\DejaEmargeException;
use App\Exceptions\Emargement\JetonExpireException;
use App\Exceptions\Emargement\JetonInvalideException;
use App\Exceptions\Seance\SeanceNonActiveException;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\SessionEmargement;
use App\Models\User;
use App\Services\EmargementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmargementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmargementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmargementService();
    }

    public function test_peut_demarrer_une_session_emargement()
    {
        $seance = Seance::factory()->create();

        $session = $this->service->demarrerSession($seance);

        $this->assertInstanceOf(SessionEmargement::class, $session);
        $this->assertEquals($seance->id, $session->seance_id);
        $this->assertNotNull($session->jeton);
        $this->assertNotNull($session->expire_a);
    }

    public function test_peut_valider_une_presence_avec_un_jeton_valide()
    {
        $now = Carbon::now();
        $seance = Seance::factory()->create([
            'debut_a' => $now->copy()->subHour(),
            'fin_a' => $now->copy()->addHour(),
        ]);

        $session = $this->service->demarrerSession($seance);
        $etudiant = User::factory()->create(['groupe_id' => $seance->groupe_id]);

        $presence = $this->service->validerPresenceParJeton($session->jeton, $etudiant);

        $this->assertInstanceOf(Presence::class, $presence);
        $this->assertEquals($session->id, $presence->session_emargement_id);
        $this->assertEquals($etudiant->id, $presence->etudiant_id);
        $this->assertEquals('present', $presence->statut);
    }

    public function test_leve_exception_si_jeton_invalide()
    {
        $etudiant = User::factory()->create();

        $this->expectException(JetonInvalideException::class);
        $this->service->validerPresenceParJeton('jeton-inexistant', $etudiant);
    }

    public function test_leve_exception_si_jeton_expire()
    {
        $seance = Seance::factory()->create();
        SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
            'jeton' => 'expire-token',
            'expire_a' => Carbon::now()->subMinute(),
        ]);
        $etudiant = User::factory()->create();

        $this->expectException(JetonExpireException::class);
        $this->service->validerPresenceParJeton('expire-token', $etudiant);
    }

    public function test_leve_exception_si_seance_pas_active()
    {
        $now = Carbon::now();
        $seance = Seance::factory()->create([
            'debut_a' => $now->copy()->subHours(5),
            'fin_a' => $now->copy()->subHours(3), // Séance terminée
        ]);

        SessionEmargement::factory()->create([
            'seance_id' => $seance->id,
            'jeton' => 'token-seance-finie',
            'expire_a' => Carbon::now()->addMinutes(10),
        ]);
        $etudiant = User::factory()->create();

        $this->expectException(SeanceNonActiveException::class);
        $this->service->validerPresenceParJeton('token-seance-finie', $etudiant);
    }

    public function test_leve_exception_si_deja_emarge()
    {
        $now = Carbon::now();
        $seance = Seance::factory()->create([
            'debut_a' => $now->copy()->subHour(),
            'fin_a' => $now->copy()->addHour(),
        ]);

        $session = $this->service->demarrerSession($seance);
        $etudiant = User::factory()->create(['groupe_id' => $seance->groupe_id]);

        // Premier émargement
        $this->service->validerPresenceParJeton($session->jeton, $etudiant);

        // Deuxième émargement identique
        $this->expectException(DejaEmargeException::class);
        $this->service->validerPresenceParJeton($session->jeton, $etudiant);
    }

    public function test_peut_rafraichir_un_jeton()
    {
        $seance = Seance::factory()->create();
        $session = $this->service->demarrerSession($seance);
        $ancienJeton = $session->jeton;

        sleep(1); // Pour s'assurer que l'expiration change
        $sessionUpdated = $this->service->rafraichirJeton($session);

        $this->assertNotEquals($ancienJeton, $sessionUpdated->jeton);
        $this->assertTrue($sessionUpdated->expire_a->isFuture());
    }
}
