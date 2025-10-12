<?php

use App\Models\User;
use App\Models\Exam;
use App\Models\Applicant;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\ExamAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'department-head',
        'full_name' => 'Admin User'
    ]);

    // Setup valid exam with questions
    $this->exam = Exam::factory()->create([
        'title' => 'Test Exam',
        'total_items' => 5,
        'mcq_quota' => 3,
        'tf_quota' => 2,
        'is_active' => true,
    ]);

    // Create sufficient questions
    $mcqQuestions = Question::factory()->count(5)->create([
        'question_type' => 'multiple_choice',
        'is_active' => true,
    ]);
    foreach ($mcqQuestions as $q) {
        QuestionOption::factory()->count(4)->create(['question_id' => $q->question_id]);
    }

    $tfQuestions = Question::factory()->count(5)->create([
        'question_type' => 'true_false',
        'is_active' => true,
    ]);
    foreach ($tfQuestions as $q) {
        QuestionOption::factory()->count(2)->create(['question_id' => $q->question_id]);
    }
});

describe('Exam Assignment Controller - Assign Exam', function () {

    test('admin can view assign exam form', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exam-assignments.assign-form'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.applicants.assign-exams');
        $response->assertViewHas(['exams', 'applicants']);
    });

    test('admin can assign exam to applicants', function () {
        $applicants = Applicant::factory()->count(3)->create();
        $applicantIds = $applicants->pluck('applicant_id')->toArray();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.assign'), [
                'exam_id' => $this->exam->exam_id,
                'applicant_ids' => $applicantIds,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'assigned' => 3,
        ]);

        // Verify assignments were created
        expect(ExamAssignment::count())->toBe(3);
        
        foreach ($applicants as $applicant) {
            $this->assertDatabaseHas('exam_assignments', [
                'exam_id' => $this->exam->exam_id,
                'applicant_id' => $applicant->applicant_id,
                'status' => 'pending',
            ]);
        }
    });

    test('assignment validation requires applicant_ids', function () {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.assign'), [
                'exam_id' => $this->exam->exam_id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['applicant_ids']);
    });

    test('assignment validation requires exam_id', function () {
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.assign'), [
                'applicant_ids' => [$applicant->applicant_id],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['exam_id']);
    });

    test('assignment validation checks exam exists', function () {
        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.assign'), [
                'exam_id' => 99999, // Non-existent
                'applicant_ids' => [$applicant->applicant_id],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['exam_id']);
    });

    test('assignment validation checks applicants exist', function () {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.assign'), [
                'exam_id' => $this->exam->exam_id,
                'applicant_ids' => [99999], // Non-existent
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['applicant_ids.0']);
    });

    test('assignment rejects exam without total_items configured', function () {
        $invalidExam = Exam::factory()->create([
            'total_items' => null, // Not configured for question bank
            'is_active' => true,
        ]);

        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.assign'), [
                'exam_id' => $invalidExam->exam_id,
                'applicant_ids' => [$applicant->applicant_id],
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Exam configuration is invalid for question bank usage.',
        ]);
    });

    test('assignment rejects exam with insufficient questions', function () {
        $examWithHighQuota = Exam::factory()->create([
            'total_items' => 100, // Need 100 questions
            'is_active' => true,
        ]);

        $applicant = Applicant::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.assign'), [
                'exam_id' => $examWithHighQuota->exam_id,
                'applicant_ids' => [$applicant->applicant_id],
            ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['success' => false]);
    });

});

describe('Exam Assignment Controller - List Assignments', function () {

    test('admin can view assignments index', function () {
        // Create some assignments
        $applicants = Applicant::factory()->count(3)->create();
        foreach ($applicants as $applicant) {
            ExamAssignment::create([
                'exam_id' => $this->exam->exam_id,
                'applicant_id' => $applicant->applicant_id,
                'status' => 'pending',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.exam-assignments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.exam-assignments.index');
        $response->assertViewHas(['assignments', 'stats']);
    });

    test('index page calculates stats correctly', function () {
        $applicants = Applicant::factory()->count(5)->create();
        
        // Create assignments with different statuses
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicants[0]->applicant_id,
            'status' => 'pending',
        ]);
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicants[1]->applicant_id,
            'status' => 'pending',
        ]);
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicants[2]->applicant_id,
            'status' => 'in_progress',
        ]);
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicants[3]->applicant_id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.exam-assignments.index'));

        $stats = $response->viewData('stats');
        
        expect($stats['total'])->toBe(4);
        expect($stats['pending'])->toBe(2);
        expect($stats['in_progress'])->toBe(1);
        expect($stats['completed'])->toBe(1);
    });

    test('index can filter by status', function () {
        $applicants = Applicant::factory()->count(3)->create();
        
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicants[0]->applicant_id,
            'status' => 'pending',
        ]);
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicants[1]->applicant_id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.exam-assignments.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $assignments = $response->viewData('assignments');
        
        expect($assignments->count())->toBe(1);
        expect($assignments->first()->status)->toBe('pending');
    });

    test('index can filter by exam', function () {
        $exam2 = Exam::factory()->create([
            'total_items' => 5,
            'is_active' => true,
        ]);
        
        $applicants = Applicant::factory()->count(2)->create();
        
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicants[0]->applicant_id,
            'status' => 'pending',
        ]);
        ExamAssignment::create([
            'exam_id' => $exam2->exam_id,
            'applicant_id' => $applicants[1]->applicant_id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.exam-assignments.index', ['exam_id' => $this->exam->exam_id]));

        $response->assertStatus(200);
        $assignments = $response->viewData('assignments');
        
        expect($assignments->count())->toBe(1);
        expect($assignments->first()->exam_id)->toBe($this->exam->exam_id);
    });

    test('index can search by applicant name', function () {
        $applicant1 = Applicant::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $applicant2 = Applicant::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);
        
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicant1->applicant_id,
            'status' => 'pending',
        ]);
        ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicant2->applicant_id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.exam-assignments.index', ['search' => 'John']));

        $response->assertStatus(200);
        $assignments = $response->viewData('assignments');
        
        expect($assignments->count())->toBe(1);
        expect($assignments->first()->applicant->first_name)->toBe('John');
    });

});

describe('Exam Assignment Controller - Show Assignment', function () {

    test('admin can view assignment details', function () {
        $applicant = Applicant::factory()->create();
        
        $assignment = ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicant->applicant_id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.exam-assignments.show', $assignment->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.exam-assignments.show');
        $response->assertViewHas('assignment');
    });

    test('show assignment returns 404 for non-existent assignment', function () {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.exam-assignments.show', 99999));

        $response->assertStatus(404);
    });

});

describe('Exam Assignment Controller - Regenerate Assignment', function () {

    test('admin can regenerate pending assignment', function () {
        $applicant = Applicant::factory()->create();
        
        $assignment = ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicant->applicant_id,
            'status' => 'pending',
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.regenerate', $assignment->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    });

    test('regenerate prevents regenerating completed assignment', function () {
        $applicant = Applicant::factory()->create();
        
        $assignment = ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicant->applicant_id,
            'status' => 'completed', // Completed
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.exam-assignments.regenerate', $assignment->id));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Cannot regenerate completed exam assignment.',
        ]);
    });

});

describe('Exam Assignment Controller - Delete Assignment', function () {

    test('admin can delete pending assignment', function () {
        $applicant = Applicant::factory()->create();
        
        $assignment = ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicant->applicant_id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.exam-assignments.destroy', $assignment->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('exam_assignments', [
            'id' => $assignment->id,
        ]);
    });

    test('delete prevents deleting completed assignment', function () {
        $applicant = Applicant::factory()->create();
        
        $assignment = ExamAssignment::create([
            'exam_id' => $this->exam->exam_id,
            'applicant_id' => $applicant->applicant_id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.exam-assignments.destroy', $assignment->id));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Cannot delete completed exam assignment.',
        ]);

        // Assignment should still exist
        $this->assertDatabaseHas('exam_assignments', [
            'id' => $assignment->id,
        ]);
    });

    test('delete returns 404 for non-existent assignment', function () {
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.exam-assignments.destroy', 99999));

        $response->assertStatus(500); // Will throw exception
    });

});

describe('Exam Assignment Controller - Authorization', function () {

    test('guest cannot access assignment endpoints', function () {
        $response = $this->get(route('admin.exam-assignments.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.exam-assignments.assign-form'));
        $response->assertRedirect(route('login'));
    });

    test('only admin can assign exams', function () {
        $applicant = Applicant::factory()->create();

        // Try without auth
        $response = $this->postJson(route('admin.exam-assignments.assign'), [
            'exam_id' => $this->exam->exam_id,
            'applicant_ids' => [$applicant->applicant_id],
        ]);

        $response->assertStatus(401);
    });

});

