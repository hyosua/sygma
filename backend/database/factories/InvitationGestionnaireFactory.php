<?php

namespace Database\Factories;

use App\Models\InvitationGestionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationGestionnaireFactory extends Factory
{
    protected $model = InvitationGestionnaire::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'token' => Str::random(60),
            'expires_at' => now()->addDays(7),
            'used_at' => null,
        ];
    }

    public function expiree(): self
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDays(1),
        ]);
    }

    public function utilisee(): self
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => now(),
        ]);
    }
}
