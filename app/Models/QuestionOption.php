<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $primaryKey = 'option_id'; // As per ERD

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'order_number',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'order_number' => 'integer',
    ];

    /**
     * Relationships
     */

    /**
     * Get the question that this option belongs to.
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    /**
     * Get the results where this option was selected.
     */
    public function results()
    {
        return $this->hasMany(Result::class, 'selected_option_id', 'option_id');
    }

    /**
     * Scope to get only correct options
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope to get only incorrect options
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope to get options ordered by order_number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_number')->orderBy('option_id');
    }

    /**
     * Get the option letter (A, B, C, D, etc.)
     */
    public function getOptionLetterAttribute()
    {
        if ($this->order_number !== null) {
            return chr(65 + $this->order_number - 1); // A=65 in ASCII
        }
        
        // Fallback: find position among siblings
        $position = $this->question->options()->where('option_id', '<=', $this->option_id)->count();
        return chr(64 + $position); // A=65 in ASCII
    }

    /**
     * Get selection statistics for this option
     */
    public function getSelectionStatsAttribute()
    {
        $totalQuestionResults = $this->question->results()->count();
        $thisOptionSelected = $this->results()->count();
        
        return [
            'selected_count' => $thisOptionSelected,
            'selection_percentage' => $totalQuestionResults > 0 
                ? round(($thisOptionSelected / $totalQuestionResults) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Check if this is the most selected option for the question
     */
    public function isMostSelected()
    {
        $thisOptionCount = $this->results()->count();
        
        $maxCount = $this->question->options()
            ->withCount('results')
            ->orderByDesc('results_count')
            ->first()
            ->results_count ?? 0;
            
        return $thisOptionCount === $maxCount && $maxCount > 0;
    }

    /**
     * Get formatted option display
     */
    public function getFormattedOptionAttribute()
    {
        return $this->option_letter . '. ' . $this->option_text;
    }
}