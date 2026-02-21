<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Presence>
 */
class PresenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statut = $this->faker->randomElement(['present', 'absent']);

        return [
            // session_emargement_id et etudiant_id seront fournis à la création
            'statut' => $statut,
            'scanne_a' => $statut === 'present' ? now() : null,
            'notes' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }
}
