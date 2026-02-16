<?php

namespace Database\Factories;

use App\Models\Groupe;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'premiere_connexion' => true,
            'url_image_profil' => null,
            'ine' => null,
            'specialites' => null,
            'groupe_id' => null,
        ];
    }

    /**
     * State pour un étudiant.
     */
    public function etudiant(): static
    {
        return $this->state(fn (array $attributes) => [
            'ine' => fake()->bothify('##########?'),
            'groupe_id' => Groupe::factory(),
        ]);
    }

    /**
     * State pour un enseignant.
     */
    public function enseignant(): static
    {
        return $this->state(fn (array $attributes) => [
            'specialites' => fake()->randomElements(['PHP', 'Laravel', 'React', 'Docker', 'SQL'], 2),
            'premiere_connexion' => false,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
