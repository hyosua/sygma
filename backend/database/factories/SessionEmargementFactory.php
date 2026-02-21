<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionEmargement>
 */
class SessionEmargementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // seance_id sera fourni à la création
            'methode' => 'qr',
            'jeton' => Str::random(32),
            'expire_a' => now()->addHours(4),
            'latitude' => null,
            'longitude' => null,
        ];
    }
}
