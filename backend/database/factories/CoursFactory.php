<?php

namespace Database\Factories;

use App\Models\Cours;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoursFactory extends Factory
{
    protected $model = Cours::class;

    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->randomElement(['Développement Web', 'Réseaux', 'Base de données', 'Algorithmique']),
        ];
    }
}
