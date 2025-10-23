<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Exam;

class QuestionBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the active exam or create one if none exists
        $exam = Exam::where('is_active', true)->first();
        
        if (!$exam) {
            $exam = Exam::create([
                'title' => 'Information Technology Assessment',
                'duration_minutes' => 30,
                'total_items' => 10,
                'mcq_quota' => 5,
                'tf_quota' => 5,
                'description' => 'IT fundamentals assessment covering programming, networking, and computer science basics',
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ]);
        } else {
            // Update exam settings for IT assessment
            $exam->update([
                'title' => 'Information Technology Assessment',
                'total_items' => 10,
                'mcq_quota' => 5,
                'tf_quota' => 5,
                'description' => 'IT fundamentals assessment covering programming, networking, and computer science basics',
            ]);
        }

        // Delete existing questions and options for fresh start
        $this->command->info('Deleting existing questions for exam: ' . $exam->title);
        foreach ($exam->questions as $question) {
            $question->options()->delete();
        }
        $exam->questions()->delete();

        $this->createMCQQuestions($exam->exam_id);
        $this->createTrueFalseQuestions($exam->exam_id);
        
        $this->command->info('✓ Successfully seeded 10 IT questions (5 MCQ + 5 T/F)');
    }

    private function createMCQQuestions($examId): void
    {
        $mcqQuestions = [
            [
                'question' => 'What does HTML stand for?',
                'options' => ['Hyper Text Markup Language', 'High Tech Modern Language', 'Home Tool Markup Language', 'Hyperlinks and Text Markup Language'],
                'correct' => 0,
                'explanation' => 'HTML stands for Hyper Text Markup Language, the standard markup language for creating web pages.',
                'points' => 1
            ],
            [
                'question' => 'Which programming language is known as the "language of the web"?',
                'options' => ['Python', 'Java', 'JavaScript', 'C++'],
                'correct' => 2,
                'explanation' => 'JavaScript is widely known as the language of the web, running in every modern browser.',
                'points' => 1
            ],
            [
                'question' => 'What is the main function of an operating system?',
                'options' => ['Virus protection', 'Manage computer hardware and software', 'Word processing', 'Internet browsing'],
                'correct' => 1,
                'explanation' => 'An operating system manages computer hardware and software resources and provides common services for computer programs.',
                'points' => 1
            ],
            [
                'question' => 'In networking, what does IP stand for?',
                'options' => ['Internet Protocol', 'Internal Process', 'Integrated Platform', 'Information Provider'],
                'correct' => 0,
                'explanation' => 'IP stands for Internet Protocol, which is responsible for addressing and routing data across networks.',
                'points' => 1
            ],
            [
                'question' => 'Which data structure uses LIFO (Last In, First Out) principle?',
                'options' => ['Queue', 'Stack', 'Array', 'Linked List'],
                'correct' => 1,
                'explanation' => 'A Stack follows the LIFO principle where the last element added is the first one to be removed.',
                'points' => 1
            ],
        ];

        foreach ($mcqQuestions as $index => $questionData) {
            $question = Question::create([
                'exam_id' => $examId,
                'question_text' => $questionData['question'],
                'question_type' => 'multiple_choice',
                'points' => $questionData['points'] ?? 1,
                'order_number' => $index + 1,
                'explanation' => $questionData['explanation'] ?? null,
                'is_active' => true,
            ]);

            foreach ($questionData['options'] as $optionIndex => $optionText) {
                QuestionOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => $optionText,
                    'is_correct' => $optionIndex === $questionData['correct'],
                    'order_number' => $optionIndex + 1,
                ]);
            }
        }
    }

    private function createTrueFalseQuestions($examId): void
    {
        $tfQuestions = [
            [
                'question' => 'SQL stands for Structured Query Language.',
                'correct' => true,
                'explanation' => 'SQL stands for Structured Query Language, used for managing relational databases.',
                'points' => 1
            ],
            [
                'question' => 'Python is a compiled programming language.',
                'correct' => false,
                'explanation' => 'Python is an interpreted language, not compiled. It executes code line by line.',
                'points' => 1
            ],
            [
                'question' => 'HTTP stands for HyperText Transfer Protocol.',
                'correct' => true,
                'explanation' => 'HTTP is the foundation protocol for data communication on the World Wide Web.',
                'points' => 1
            ],
            [
                'question' => 'RAM is a type of permanent storage.',
                'correct' => false,
                'explanation' => 'RAM (Random Access Memory) is volatile memory that loses data when power is off.',
                'points' => 1
            ],
            [
                'question' => 'A byte consists of 8 bits.',
                'correct' => true,
                'explanation' => 'A byte is the fundamental unit of computer storage consisting of 8 bits.',
                'points' => 1
            ],
        ];

        foreach ($tfQuestions as $index => $questionData) {
            $question = Question::create([
                'exam_id' => $examId,
                'question_text' => $questionData['question'],
                'question_type' => 'true_false',
                'correct_answer' => $questionData['correct'],
                'points' => $questionData['points'] ?? 1,
                'order_number' => 6 + $index, // Start after MCQ questions (5 MCQ + T/F)
                'explanation' => $questionData['explanation'] ?? null,
                'is_active' => true,
            ]);

            // Create True and False options
            QuestionOption::create([
                'question_id' => $question->question_id,
                'option_text' => 'True',
                'is_correct' => $questionData['correct'] === true,
                'order_number' => 1,
            ]);

            QuestionOption::create([
                'question_id' => $question->question_id,
                'option_text' => 'False',
                'is_correct' => $questionData['correct'] === false,
                'order_number' => 2,
            ]);
        }
    }
}
