<?php

namespace Database\Factories;

use App\Models\Cours;
use App\Models\Groupe;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeanceFactory extends Factory
{
    protected $model = Seance::class;

    public function definition(): array
    {
        return [
            'cours_id' => Cours::factory(),
            'enseignant_id' => User::factory()->enseignant(),
            'groupe_id' => Groupe::factory(),
            'debut_a' => now()->addHour(),
            'fin_a' => now()->addHours(4),
        ];
    }
}
