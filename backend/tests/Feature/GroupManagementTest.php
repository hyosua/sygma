<?php

namespace Tests\Feature;

use App\Models\Groupe;
use App\Models\User;
use App\Models\Seance;
use App\Models\Cours;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_peut_creer_groupes_et_assigner_des_utilisateurs()
    {
        $groupe = Groupe::factory()->create(['nom' => 'LP Dawii']);
        $user = User::factory()->etudiant()->create(['groupe_id' => $groupe->id]);

        $this->assertEquals('LP Dawii', $user->groupe->nom);
        $this->assertNotNull($user->ine);
    }

    public function test_seance_peut_etre_liee_a_un_groupe()
    {
        $groupe = Groupe::factory()->create(['nom' => 'LP ASRI']);
        $seance = Seance::factory()->create(['groupe_id' => $groupe->id]);

        $this->assertEquals('LP ASRI', $seance->groupe->nom);
    }
}
