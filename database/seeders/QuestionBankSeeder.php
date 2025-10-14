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
                'title' => 'General Knowledge Assessment',
                'duration_minutes' => 60,
                'total_items' => 50,
                'mcq_quota' => 25,
                'tf_quota' => 25,
                'description' => 'Comprehensive assessment covering various topics',
                'is_active' => true,
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ]);
        }

        $this->createMCQQuestions($exam->exam_id);
        $this->createTrueFalseQuestions($exam->exam_id);
    }

    private function createMCQQuestions($examId): void
    {
        $mcqQuestions = [
            [
                'question' => 'What is the capital of France?',
                'options' => ['London', 'Berlin', 'Paris', 'Madrid'],
                'correct' => 2, // Paris
                'explanation' => 'Paris has been the capital of France since the 6th century.'
            ],
            [
                'question' => 'Which planet is known as the Red Planet?',
                'options' => ['Venus', 'Mars', 'Jupiter', 'Saturn'],
                'correct' => 1, // Mars
                'explanation' => 'Mars appears red due to iron oxide on its surface.'
            ],
            [
                'question' => 'What is the largest ocean on Earth?',
                'options' => ['Atlantic', 'Indian', 'Pacific', 'Arctic'],
                'correct' => 2, // Pacific
                'explanation' => 'The Pacific Ocean covers more than 30% of Earth\'s surface.'
            ],
            [
                'question' => 'Who wrote "Romeo and Juliet"?',
                'options' => ['Charles Dickens', 'William Shakespeare', 'Mark Twain', 'Jane Austen'],
                'correct' => 1, // Shakespeare
                'explanation' => 'William Shakespeare wrote this famous tragedy in the late 16th century.'
            ],
            [
                'question' => 'What is the chemical symbol for gold?',
                'options' => ['Go', 'Gd', 'Au', 'Ag'],
                'correct' => 2, // Au
                'explanation' => 'Au comes from the Latin word "aurum" meaning gold.'
            ],
            [
                'question' => 'Which country has the most natural lakes?',
                'options' => ['Russia', 'Canada', 'United States', 'Finland'],
                'correct' => 1, // Canada
                'explanation' => 'Canada has over 2 million lakes, more than any other country.'
            ],
            [
                'question' => 'What is the smallest country in the world?',
                'options' => ['Monaco', 'Vatican City', 'Liechtenstein', 'San Marino'],
                'correct' => 1, // Vatican City
                'explanation' => 'Vatican City covers only 0.17 square miles.'
            ],
            [
                'question' => 'Which element has the atomic number 1?',
                'options' => ['Helium', 'Hydrogen', 'Lithium', 'Carbon'],
                'correct' => 1, // Hydrogen
                'explanation' => 'Hydrogen is the first element in the periodic table.'
            ],
            [
                'question' => 'What is the longest river in the world?',
                'options' => ['Amazon', 'Nile', 'Mississippi', 'Yangtze'],
                'correct' => 1, // Nile
                'explanation' => 'The Nile River is approximately 4,135 miles long.'
            ],
            [
                'question' => 'Which programming language was created by Guido van Rossum?',
                'options' => ['Java', 'Python', 'C++', 'JavaScript'],
                'correct' => 1, // Python
                'explanation' => 'Python was created by Guido van Rossum in 1991.'
            ],
            [
                'question' => 'What is the speed of light in vacuum?',
                'options' => ['300,000 km/s', '299,792,458 m/s', '186,000 miles/s', 'All of the above'],
                'correct' => 3, // All of the above
                'explanation' => 'All these values represent the speed of light in different units.'
            ],
            [
                'question' => 'Which mountain is the highest in the world?',
                'options' => ['K2', 'Mount Everest', 'Kangchenjunga', 'Lhotse'],
                'correct' => 1, // Mount Everest
                'explanation' => 'Mount Everest is 8,848 meters above sea level.'
            ],
            [
                'question' => 'What is the currency of Japan?',
                'options' => ['Won', 'Yuan', 'Yen', 'Baht'],
                'correct' => 2, // Yen
                'explanation' => 'The Japanese Yen is the official currency of Japan.'
            ],
            [
                'question' => 'Which gas makes up most of Earth\'s atmosphere?',
                'options' => ['Oxygen', 'Carbon Dioxide', 'Nitrogen', 'Argon'],
                'correct' => 2, // Nitrogen
                'explanation' => 'Nitrogen makes up about 78% of Earth\'s atmosphere.'
            ],
            [
                'question' => 'Who painted the Mona Lisa?',
                'options' => ['Vincent van Gogh', 'Pablo Picasso', 'Leonardo da Vinci', 'Michelangelo'],
                'correct' => 2, // Leonardo da Vinci
                'explanation' => 'Leonardo da Vinci painted the Mona Lisa between 1503-1519.'
            ],
            [
                'question' => 'What is the largest mammal in the world?',
                'options' => ['African Elephant', 'Blue Whale', 'Giraffe', 'Polar Bear'],
                'correct' => 1, // Blue Whale
                'explanation' => 'Blue whales can grow up to 100 feet long and weigh 200 tons.'
            ],
            [
                'question' => 'Which year did World War II end?',
                'options' => ['1944', '1945', '1946', '1947'],
                'correct' => 1, // 1945
                'explanation' => 'World War II ended in 1945 with Japan\'s surrender.'
            ],
            [
                'question' => 'What is the hardest natural substance on Earth?',
                'options' => ['Gold', 'Iron', 'Diamond', 'Quartz'],
                'correct' => 2, // Diamond
                'explanation' => 'Diamond is the hardest known natural material.'
            ],
            [
                'question' => 'Which planet is closest to the Sun?',
                'options' => ['Venus', 'Mercury', 'Earth', 'Mars'],
                'correct' => 1, // Mercury
                'explanation' => 'Mercury is the closest planet to the Sun in our solar system.'
            ],
            [
                'question' => 'What does CPU stand for?',
                'options' => ['Central Processing Unit', 'Computer Processing Unit', 'Central Program Unit', 'Computer Program Unit'],
                'correct' => 0, // Central Processing Unit
                'explanation' => 'CPU stands for Central Processing Unit, the brain of a computer.'
            ],
            [
                'question' => 'Which country is known as the Land of the Rising Sun?',
                'options' => ['China', 'Japan', 'South Korea', 'Thailand'],
                'correct' => 1, // Japan
                'explanation' => 'Japan is called the Land of the Rising Sun due to its eastern location.'
            ],
            [
                'question' => 'What is the largest desert in the world?',
                'options' => ['Sahara', 'Antarctic', 'Arabian', 'Gobi'],
                'correct' => 1, // Antarctic
                'explanation' => 'The Antarctic Desert is the largest desert, covering 5.5 million square miles.'
            ],
            [
                'question' => 'Which element is essential for human blood?',
                'options' => ['Calcium', 'Iron', 'Sodium', 'Potassium'],
                'correct' => 1, // Iron
                'explanation' => 'Iron is essential for hemoglobin production in red blood cells.'
            ],
            [
                'question' => 'What is the smallest unit of matter?',
                'options' => ['Molecule', 'Atom', 'Electron', 'Proton'],
                'correct' => 1, // Atom
                'explanation' => 'An atom is the smallest unit of matter that retains chemical properties.'
            ],
            [
                'question' => 'Which programming paradigm does Python primarily follow?',
                'options' => ['Functional', 'Object-Oriented', 'Procedural', 'All of the above'],
                'correct' => 3, // All of the above
                'explanation' => 'Python supports multiple programming paradigms including OOP, functional, and procedural.'
            ]
        ];

        foreach ($mcqQuestions as $index => $questionData) {
            $question = Question::create([
                'exam_id' => $examId,
                'question_text' => $questionData['question'],
                'question_type' => 'multiple_choice',
                'points' => 1,
                'order_number' => $index + 1,
                'explanation' => $questionData['explanation'],
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
                'question' => 'The Great Wall of China is visible from space with the naked eye.',
                'correct' => false,
                'explanation' => 'This is a common myth. The Great Wall is not visible from space with the naked eye.'
            ],
            [
                'question' => 'Sharks are mammals.',
                'correct' => false,
                'explanation' => 'Sharks are fish, not mammals. They breathe through gills.'
            ],
            [
                'question' => 'The human brain uses approximately 20% of the body\'s energy.',
                'correct' => true,
                'explanation' => 'The brain uses about 20% of the body\'s total energy consumption.'
            ],
            [
                'question' => 'Goldfish have a memory span of only 3 seconds.',
                'correct' => false,
                'explanation' => 'Goldfish actually have a memory span of several months.'
            ],
            [
                'question' => 'The Earth is closer to the Sun in winter (Northern Hemisphere).',
                'correct' => true,
                'explanation' => 'Earth is closest to the Sun in early January, which is winter in the Northern Hemisphere.'
            ],
            [
                'question' => 'Bats are blind.',
                'correct' => false,
                'explanation' => 'Bats can see, though they rely more on echolocation for navigation.'
            ],
            [
                'question' => 'The human body has 206 bones.',
                'correct' => true,
                'explanation' => 'An adult human skeleton typically has 206 bones.'
            ],
            [
                'question' => 'Lightning never strikes the same place twice.',
                'correct' => false,
                'explanation' => 'Lightning can and does strike the same place multiple times.'
            ],
            [
                'question' => 'The Great Pyramid of Giza is the oldest of the Seven Wonders.',
                'correct' => true,
                'explanation' => 'The Great Pyramid is the oldest and only remaining ancient wonder.'
            ],
            [
                'question' => 'Humans and giraffes have the same number of neck vertebrae.',
                'correct' => true,
                'explanation' => 'Both humans and giraffes have 7 cervical vertebrae.'
            ],
            [
                'question' => 'The tongue is the strongest muscle in the human body.',
                'correct' => false,
                'explanation' => 'The masseter (jaw muscle) is actually the strongest muscle relative to its size.'
            ],
            [
                'question' => 'Mount Everest is growing taller each year.',
                'correct' => true,
                'explanation' => 'Mount Everest grows about 4mm taller each year due to tectonic activity.'
            ],
            [
                'question' => 'The human heart has four chambers.',
                'correct' => true,
                'explanation' => 'The human heart has two atria and two ventricles.'
            ],
            [
                'question' => 'The Sahara Desert is the largest desert in the world.',
                'correct' => false,
                'explanation' => 'The Antarctic Desert is actually the largest desert in the world.'
            ],
            [
                'question' => 'Water boils at 100°C at sea level.',
                'correct' => true,
                'explanation' => 'Water boils at exactly 100°C (212°F) at standard atmospheric pressure.'
            ],
            [
                'question' => 'The human body is 60% water.',
                'correct' => true,
                'explanation' => 'The average adult human body is approximately 60% water.'
            ],
            [
                'question' => 'The Sun is a star.',
                'correct' => true,
                'explanation' => 'The Sun is a G-type main-sequence star.'
            ],
            [
                'question' => 'Penguins can fly.',
                'correct' => false,
                'explanation' => 'Penguins cannot fly, but they are excellent swimmers.'
            ],
            [
                'question' => 'The speed of sound is faster than the speed of light.',
                'correct' => false,
                'explanation' => 'Light travels much faster than sound. Light: ~300,000 km/s, Sound: ~343 m/s.'
            ],
            [
                'question' => 'The human brain contains approximately 86 billion neurons.',
                'correct' => true,
                'explanation' => 'The human brain contains approximately 86 billion neurons.'
            ],
            [
                'question' => 'Dolphins are fish.',
                'correct' => false,
                'explanation' => 'Dolphins are mammals, not fish. They breathe air and nurse their young.'
            ],
            [
                'question' => 'The Amazon River is the longest river in the world.',
                'correct' => false,
                'explanation' => 'The Nile River is longer than the Amazon River.'
            ],
            [
                'question' => 'The human body produces vitamin D when exposed to sunlight.',
                'correct' => true,
                'explanation' => 'The skin produces vitamin D when exposed to UVB radiation from sunlight.'
            ],
            [
                'question' => 'The Moon is larger than Earth.',
                'correct' => false,
                'explanation' => 'The Moon is much smaller than Earth, with about 1/4 Earth\'s diameter.'
            ],
            [
                'question' => 'The human heart beats approximately 100,000 times per day.',
                'correct' => true,
                'explanation' => 'The average human heart beats about 100,000 times per day.'
            ]
        ];

        foreach ($tfQuestions as $index => $questionData) {
            $question = Question::create([
                'exam_id' => $examId,
                'question_text' => $questionData['question'],
                'question_type' => 'true_false',
                'correct_answer' => $questionData['correct'],
                'points' => 1,
                'order_number' => 26 + $index, // Start after MCQ questions
                'explanation' => $questionData['explanation'],
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
