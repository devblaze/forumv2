<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->text,
            'user_id' => \App\Models\User::factory(),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'is_published' => $this->faker->boolean,
        ];
    }
}
