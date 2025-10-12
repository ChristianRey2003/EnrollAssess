<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamSettingsUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $exam;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'department-head',
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
        ]);

        // Create a test exam
        $this->exam = Exam::create([
            'title' => 'Test Exam',
            'description' => 'Test Description',
            'duration_minutes' => 60,
            'total_items' => 50,
            'mcq_quota' => 35,
            'tf_quota' => 15,
            'is_active' => false,
        ]);

        // Create some questions for quota validation
        Question::factory()->count(40)->create([
            'exam_id' => $this->exam->exam_id,
            'question_type' => 'multiple_choice',
            'is_active' => true,
        ]);

        Question::factory()->count(20)->create([
            'exam_id' => $this->exam->exam_id,
            'question_type' => 'true_false',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_can_update_exam_settings_successfully()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'duration_minutes' => 90,
                'total_items' => 60,
                'mcq_quota' => 40,
                'tf_quota' => 20,
                'is_active' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Exam settings updated successfully!',
            ]);

        $this->assertDatabaseHas('exams', [
            'exam_id' => $this->exam->exam_id,
            'duration_minutes' => 90,
            'total_items' => 60,
            'mcq_quota' => 40,
            'tf_quota' => 20,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_validates_duration_is_required_and_positive()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'duration_minutes' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['duration_minutes']);
    }

    /** @test */
    public function it_validates_total_items_is_positive()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'total_items' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_items']);
    }

    /** @test */
    public function it_rejects_when_quotas_exceed_total_items()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'total_items' => 50,
                'mcq_quota' => 40,
                'tf_quota' => 20, // 40 + 20 = 60 > 50
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The sum of MCQ and True/False quotas cannot exceed total items.',
            ]);
    }

    /** @test */
    public function it_rejects_when_mcq_quota_exceeds_available_questions()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'mcq_quota' => 50, // We only have 40 MCQ questions
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['mcq_quota']
            ]);
    }

    /** @test */
    public function it_rejects_when_tf_quota_exceeds_available_questions()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'tf_quota' => 25, // We only have 20 T/F questions
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['tf_quota']
            ]);
    }

    /** @test */
    public function it_validates_ends_at_is_after_starts_at()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'starts_at' => '2025-12-31 10:00:00',
                'ends_at' => '2025-12-30 10:00:00', // Before starts_at
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ends_at']);
    }

    /** @test */
    public function it_can_update_availability_window()
    {
        $startsAt = '2025-12-01 08:00:00';
        $endsAt = '2025-12-31 23:59:59';

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->exam->refresh();
        $this->assertEquals($startsAt, $this->exam->starts_at->format('Y-m-d H:i:s'));
        $this->assertEquals($endsAt, $this->exam->ends_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_can_clear_availability_window()
    {
        // First set values
        $this->exam->update([
            'starts_at' => '2025-12-01 08:00:00',
            'ends_at' => '2025-12-31 23:59:59',
        ]);

        // Then clear them
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'starts_at' => null,
                'ends_at' => null,
            ]);

        $response->assertStatus(200);
        
        $this->exam->refresh();
        $this->assertNull($this->exam->starts_at);
        $this->assertNull($this->exam->ends_at);
    }

    /** @test */
    public function it_returns_updated_exam_data_with_formatted_duration()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'duration_minutes' => 120,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('exam.duration_minutes', 120)
            ->assertJsonPath('exam.formatted_duration', '2h 0m');
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->putJson("/admin/exams/{$this->exam->exam_id}", [
            'duration_minutes' => 90,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_admin_or_department_head_role()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);

        $response = $this->actingAs($instructor)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'duration_minutes' => 90,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_partially_update_exam_settings()
    {
        // Update only duration, leaving other fields unchanged
        $response = $this->actingAs($this->admin)
            ->putJson("/admin/exams/{$this->exam->exam_id}", [
                'duration_minutes' => 120,
            ]);

        $response->assertStatus(200);

        $this->exam->refresh();
        $this->assertEquals(120, $this->exam->duration_minutes);
        $this->assertEquals(50, $this->exam->total_items); // Unchanged
        $this->assertEquals(35, $this->exam->mcq_quota); // Unchanged
    }
}

