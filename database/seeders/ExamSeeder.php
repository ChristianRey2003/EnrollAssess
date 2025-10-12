<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionOption;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the main BSIT entrance exam with question quotas
        $exam = Exam::updateOrCreate(
            ['title' => 'BSIT Entrance Examination'],
            [
                'title' => 'BSIT Entrance Examination',
                'duration_minutes' => 90,
                'description' => 'Comprehensive entrance examination for BSIT program with randomized questions.',
                'is_active' => true,
                'total_items' => 20,
                'mcq_quota' => 15,
                'tf_quota' => 5,
            ]
        );

        // Sample questions
        $questions = [
            [
                'question_text' => 'What does CPU stand for?',
                'question_type' => 'multiple_choice',
                'points' => 2,
                'order_number' => 1,
                'options' => [
                    ['option_text' => 'Computer Processing Unit', 'is_correct' => false, 'order_number' => 1],
                    ['option_text' => 'Central Processing Unit', 'is_correct' => true, 'order_number' => 2],
                    ['option_text' => 'Core Processing Unit', 'is_correct' => false, 'order_number' => 3],
                    ['option_text' => 'Central Program Unit', 'is_correct' => false, 'order_number' => 4],
                ]
            ],
            [
                'question_text' => 'Which is a programming language?',
                'question_type' => 'multiple_choice',
                'points' => 2,
                'order_number' => 2,
                'options' => [
                    ['option_text' => 'Python', 'is_correct' => true, 'order_number' => 1],
                    ['option_text' => 'HTML', 'is_correct' => false, 'order_number' => 2],
                    ['option_text' => 'CSS', 'is_correct' => false, 'order_number' => 3],
                    ['option_text' => 'XML', 'is_correct' => false, 'order_number' => 4],
                ]
            ],
            // Add more questions as needed
        ];

        foreach ($questions as $questionData) {
            $options = $questionData['options'];
            unset($questionData['options']);
            
            $questionData['exam_id'] = $exam->exam_id;
            
            $question = Question::updateOrCreate(
                [
                    'exam_id' => $exam->exam_id,
                    'order_number' => $questionData['order_number']
                ],
                $questionData
            );

            foreach ($options as $optionData) {
                $optionData['question_id'] = $question->question_id;
                
                QuestionOption::updateOrCreate(
                    [
                        'question_id' => $question->question_id,
                        'order_number' => $optionData['order_number']
                    ],
                    $optionData
                );
            }
        }

        $this->command->info('Exams seeded successfully!');
    }
}