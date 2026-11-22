<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

// Factory: pabrik data dummy memakai Faker.
class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true), // unique() menjaga judul tidak kembar
            'type' => fake()->randomElement(['film', 'series', 'game', 'musik']),
            'genre' => fake()->randomElement(['aksi', 'drama', 'komedi', 'horor', 'petualangan']),
            'release_year' => fake()->numberBetween(1990, 2026),
            'description' => fake()->sentence(),
        ];
    }
}
