<?php

namespace Database\Factories;

use App\Models\Groupe;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupeFactory extends Factory
{
    protected $model = Groupe::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->randomElement(['LP Dawii', 'LP ASRI', 'M1 Informatique', 'M2 MIAGE', 'L1 Informatique', 'M2 Cybersécurité']),
            'promotion' => '2025-2026',
        ];
    }
}
