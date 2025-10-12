<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'duration_minutes' => fake()->randomElement([60, 90, 120]),
            'total_items' => null, // Set explicitly when needed
            'mcq_quota' => null,
            'tf_quota' => null,
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the exam is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the exam uses question bank with quotas.
     */
    public function withQuotas(int $total, ?int $mcq = null, ?int $tf = null): static
    {
        return $this->state(fn (array $attributes) => [
            'total_items' => $total,
            'mcq_quota' => $mcq,
            'tf_quota' => $tf,
        ]);
    }
}

