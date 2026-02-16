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

    public function test_can_create_groups_and_assign_users()
    {
        $groupe = Groupe::factory()->create(['nom' => 'LP Dawii']);
        $user = User::factory()->etudiant()->create(['groupe_id' => $groupe->id]);

        $this->assertEquals('LP Dawii', $user->groupe->nom);
        $this->assertNotNull($user->ine);
    }

    public function test_seance_can_be_linked_to_group()
    {
        $groupe = Groupe::factory()->create(['nom' => 'LP ASRI']);
        $seance = Seance::factory()->create(['groupe_id' => $groupe->id]);

        $this->assertEquals('LP ASRI', $seance->groupe->nom);
    }
}
