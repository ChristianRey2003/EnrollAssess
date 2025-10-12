<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => null, // Required - will be set when creating questions
            'question_text' => fake()->sentence() . '?',
            'question_type' => fake()->randomElement(['multiple_choice', 'true_false']),
            'correct_answer' => null, // For TF questions
            'points' => fake()->randomElement([1, 2, 3, 5]),
            'order_number' => fake()->numberBetween(1, 100),
            'explanation' => fake()->optional()->paragraph(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the question is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the question is multiple choice.
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'multiple_choice',
            'correct_answer' => null,
        ]);
    }

    /**
     * Indicate that the question is true/false.
     */
    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'true_false',
            'correct_answer' => fake()->boolean(),
        ]);
    }

    /**
     * Indicate that the question is an essay.
     */
    public function essay(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'essay',
            'correct_answer' => null,
        ]);
    }
}

