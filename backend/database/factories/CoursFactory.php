<?php

namespace Database\Factories;

use App\Models\Cours;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoursFactory extends Factory
{
    protected $model = Cours::class;

    public function definition(): array
    {
        $courseNames = [
            'SQL', 'Wordpress', 'Java', 'PHP', 'Joomla', 'HTML',
            'Anglais', 'OGE', 'GP', 'Bash', 'Javascript', 'Python'
        ];

        return [
            'nom' => $this->faker->unique()->randomElement($courseNames),
        ];
    }
}
