<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);
        Role::create(['name' => 'etudiant', 'guard_name' => 'web']);
        Role::create(['name' => 'gestionnaire', 'guard_name' => 'web']);
    }
}
