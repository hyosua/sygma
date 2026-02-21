<?php

namespace Database\Factories;

use App\Models\Cours;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoursFactory extends Factory
{
    protected $model = Cours::class;

    public function definition(): array
    {
        // Générer un nom de cours plus varié et unique.
        // On utilise unique()->words(3, true) pour générer des mots aléatoires et garantir l'unicité
        // Si 10 cours ou plus sont créés, il est préférable d'avoir un mécanisme d'unicité plus robuste.
        static $sequence = 0;
        $sequence++;
        return [
            'nom' => $this->faker->unique()->sentence(2, true) . ' ' . $sequence,
        ];
    }
}
