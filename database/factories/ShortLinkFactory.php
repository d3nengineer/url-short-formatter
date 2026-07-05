<?php

namespace Database\Factories;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShortLink>
 */
class ShortLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'original_url' => sprintf('https://%s/%s', fake()->unique()->domainName(), fake()->slug()),
            'slug' => fake()->unique()->bothify('????####'),
            'expires_at' => null,
            'disabled_at' => null,
            'last_redirected_at' => null,
        ];
    }

    /**
     * Indicate that the short link has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinute(),
        ]);
    }

    /**
     * Indicate that the short link has been disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'disabled_at' => now(),
        ]);
    }

    /**
     * Indicate that the short link has redirected at least once.
     */
    public function redirected(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_redirected_at' => now(),
        ]);
    }

    /**
     * Indicate that the short link has been soft deleted.
     */
    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
