<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            // Drop old rubric columns
            $table->dropColumn([
                'rating_communication',
                'rating_technical',
                'rating_problem_solving'
            ]);
            
            // Add new BSIT rubric columns (8 criteria, 10 points each)
            $table->integer('communication_skills')->nullable()->after('status')->comment('Score 0-10: Communication Skills');
            $table->integer('motivation_interest')->nullable()->after('communication_skills')->comment('Score 0-10: Motivation and Interest');
            $table->integer('problem_solving_attitude')->nullable()->after('motivation_interest')->comment('Score 0-10: Problem-solving and Critical Thinking');
            $table->integer('program_understanding')->nullable()->after('problem_solving_attitude')->comment('Score 0-10: Understanding of the Program');
            $table->integer('personality_attitude')->nullable()->after('program_understanding')->comment('Score 0-10: Personality and Attitude');
            $table->integer('it_background')->nullable()->after('personality_attitude')->comment('Score 0-10: IT Exposure / Background');
            $table->integer('willingness_to_learn')->nullable()->after('it_background')->comment('Score 0-10: Willingness to Learn');
            $table->integer('overall_impression')->nullable()->after('willingness_to_learn')->comment('Score 0-10: Overall Impression');
            
            // Add evaluator notes and written feedback fields
            $table->text('evaluator_notes')->nullable()->after('notes')->comment('Evaluator notes and observations');
            $table->text('strengths')->nullable()->after('evaluator_notes')->comment('Applicant strengths');
            $table->text('areas_improvement')->nullable()->after('strengths')->comment('Areas for improvement');
            $table->string('overall_rating', 50)->nullable()->after('areas_improvement')->comment('Overall rating: excellent, very_good, good, satisfactory, needs_improvement');
            
            // Rename notes to interview_notes for clarity
            $table->renameColumn('notes', 'interview_notes');
            
            // Update recommendation enum to match new values
            $table->dropColumn('recommendation');
        });
        
        Schema::table('interviews', function (Blueprint $table) {
            $table->enum('recommendation', ['highly_recommended', 'recommended', 'conditional', 'not_recommended'])
                ->nullable()
                ->after('overall_rating')
                ->comment('Final recommendation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            // Restore old rubric columns
            $table->integer('rating_communication')->nullable();
            $table->integer('rating_technical')->nullable();
            $table->integer('rating_problem_solving')->nullable();
            
            // Drop new BSIT rubric columns
            $table->dropColumn([
                'communication_skills',
                'motivation_interest',
                'problem_solving_attitude',
                'program_understanding',
                'personality_attitude',
                'it_background',
                'willingness_to_learn',
                'overall_impression',
                'evaluator_notes',
                'strengths',
                'areas_improvement',
                'overall_rating'
            ]);
            
            // Restore original notes column name
            $table->renameColumn('interview_notes', 'notes');
            
            // Restore old recommendation enum
            $table->dropColumn('recommendation');
        });
        
        Schema::table('interviews', function (Blueprint $table) {
            $table->enum('recommendation', ['recommended', 'waitlisted', 'not-recommended'])->nullable();
        });
    }
};
