<?php

use App\Models\Applicant;
use App\Models\ExamSet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'role' => 'department-head',
        'full_name' => 'Admin User'
    ]);
});

describe('Applicant Management', function () {
    
    test('admin can view applicants index page', function () {
        // Create some test applicants
        Applicant::factory()->count(5)->create();
        
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.applicants.index'));
        
        $response->assertStatus(200)
                ->assertViewIs('admin.applicants.index')
                ->assertViewHas(['applicants', 'stats', 'examSets']);
    });

    test('admin can search applicants by name', function () {
        $applicant1 = Applicant::factory()->create(['full_name' => 'John Doe']);
        $applicant2 = Applicant::factory()->create(['full_name' => 'Jane Smith']);
        
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.applicants.index', ['search' => 'John']));
        
        $response->assertStatus(200);
        
        // Check that only John's record is in the response
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    });

    test('admin can filter applicants by status', function () {
        $pendingApplicant = Applicant::factory()->create(['status' => 'pending']);
        $completedApplicant = Applicant::factory()->create(['status' => 'exam-completed']);
        
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.applicants.index', ['status' => 'pending']));
        
        $response->assertStatus(200);
        $response->assertSee($pendingApplicant->full_name);
        $response->assertDontSee($completedApplicant->full_name);
    });

    test('admin can create new applicant', function () {
        $examSet = ExamSet::factory()->create(['is_active' => true]);
        
        $applicantData = [
            'full_name' => 'Test Applicant',
            'email_address' => 'test@example.com',
            'phone_number' => '123-456-7890',
            'education_background' => 'Computer Science',
            'exam_set_id' => $examSet->exam_set_id
        ];
        
        $response = $this->actingAs($this->adminUser)
                         ->post(route('admin.applicants.store'), $applicantData);
        
        $response->assertRedirect(route('admin.applicants.index'))
                ->assertSessionHas('success');
        
        $this->assertDatabaseHas('applicants', [
            'full_name' => 'Test Applicant',
            'email_address' => 'test@example.com'
        ]);
    });

    test('creating applicant validates required fields', function () {
        $response = $this->actingAs($this->adminUser)
                         ->post(route('admin.applicants.store'), []);
        
        $response->assertSessionHasErrors(['full_name', 'email_address']);
    });

    test('creating applicant validates unique email', function () {
        $existingApplicant = Applicant::factory()->create([
            'email_address' => 'existing@example.com'
        ]);
        
        $response = $this->actingAs($this->adminUser)
                         ->post(route('admin.applicants.store'), [
                             'full_name' => 'New Applicant',
                             'email_address' => 'existing@example.com'
                         ]);
        
        $response->assertSessionHasErrors(['email_address']);
    });

    test('admin can update existing applicant', function () {
        $applicant = Applicant::factory()->create([
            'full_name' => 'Original Name',
            'email_address' => 'original@example.com'
        ]);
        
        $updateData = [
            'full_name' => 'Updated Name',
            'email_address' => 'updated@example.com',
            'status' => 'exam-completed'
        ];
        
        $response = $this->actingAs($this->adminUser)
                         ->put(route('admin.applicants.update', $applicant->applicant_id), $updateData);
        
        $response->assertRedirect(route('admin.applicants.index'))
                ->assertSessionHas('success');
        
        $applicant->refresh();
        expect($applicant->full_name)->toBe('Updated Name');
        expect($applicant->email_address)->toBe('updated@example.com');
        expect($applicant->status)->toBe('exam-completed');
    });

    test('admin can delete applicant', function () {
        $applicant = Applicant::factory()->create();
        
        $response = $this->actingAs($this->adminUser)
                         ->delete(route('admin.applicants.destroy', $applicant->applicant_id));
        
        $response->assertRedirect(route('admin.applicants.index'))
                ->assertSessionHas('success');
        
        $this->assertDatabaseMissing('applicants', [
            'applicant_id' => $applicant->applicant_id
        ]);
    });

    test('pagination works correctly', function () {
        // Create more applicants than the default pagination size
        Applicant::factory()->count(25)->create();
        
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.applicants.index'));
        
        $response->assertStatus(200);
        
        // Check pagination links are present
        $response->assertSee('Next');
    });

});

describe('Applicant Statistics', function () {
    
    test('statistics are calculated correctly', function () {
        // Create applicants with different statuses
        Applicant::factory()->count(3)->create(['status' => 'pending']);
        Applicant::factory()->count(2)->create(['status' => 'exam-completed']);
        
        $response = $this->actingAs($this->adminUser)
                         ->get(route('admin.applicants.index'));
        
        $response->assertStatus(200);
        
        $stats = $response->viewData('stats');
        expect($stats['total'])->toBe(5);
        expect($stats['pending'])->toBe(3);
        expect($stats['exam_completed'])->toBe(2);
    });
    
});
