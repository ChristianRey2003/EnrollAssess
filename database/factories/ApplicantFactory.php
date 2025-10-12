<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Applicant>
 */
class ApplicantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        
        return [
            'application_no' => 'APP-' . fake()->unique()->numerify('####-####'),
            'first_name' => $firstName,
            'middle_name' => fake()->optional()->firstName(),
            'last_name' => $lastName,
            'preferred_course' => fake()->randomElement([
                'BS Computer Science',
                'BS Information Technology',
                'BS Computer Engineering',
                'BS Data Science',
            ]),
            'email_address' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'assigned_instructor_id' => null,
            'score' => null,
            'enrollassess_score' => null,
            'interview_score' => null,
            'verbal_description' => null,
            'status' => 'pending',
            'exam_completed_at' => null,
        ];
    }

    /**
     * Indicate that the applicant has completed the exam.
     */
    public function examCompleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'exam-completed',
            'score' => fake()->randomFloat(2, 60, 100),
            'exam_completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the applicant is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}

