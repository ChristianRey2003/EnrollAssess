<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Interview Rubric Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the scoring criteria and descriptors for
    | interview evaluations. Can be updated by stakeholders without code changes.
    |
    */

    'scoring_scale' => [
        1 => [
            'label' => 'Needs Improvement',
            'description' => 'The student shows minimal understanding and requires significant improvement.'
        ],
        2 => [
            'label' => 'Fair',
            'description' => 'The student demonstrates basic knowledge but lacks consistency and clarity.'
        ],
        3 => [
            'label' => 'Competent',
            'description' => 'Solid grasp of concepts; meets expectations with minor gaps.'
        ],
        4 => [
            'label' => 'Strong',
            'description' => 'Above average performance; clear, consistent, and reliable.'
        ],
        5 => [
            'label' => 'Excellent',
            'description' => 'Exceptional mastery; insightful and consistently high quality.'
        ],
    ],

    'evaluation_categories' => [
        'technical' => [
            'name' => 'Technical Skills',
            'weight' => 40,
            'description' => 'Programming knowledge, problem-solving, algorithms, and system design',
            'criteria' => [
                'programming' => 'Programming Fundamentals',
                'problem_solving' => 'Problem Solving Approach',
                'algorithms' => 'Algorithm Understanding',
                'system_design' => 'System Design Thinking',
            ]
        ],
        'communication' => [
            'name' => 'Communication Skills',
            'weight' => 30,
            'description' => 'Clarity of expression, listening skills, and confidence',
            'criteria' => [
                'clarity' => 'Clarity of Expression',
                'listening' => 'Active Listening',
                'confidence' => 'Confidence & Presentation',
            ]
        ],
        'analytical' => [
            'name' => 'Analytical Thinking',
            'weight' => 30,
            'description' => 'Critical thinking, creativity, and attention to detail',
            'criteria' => [
                'critical_thinking' => 'Critical Thinking',
                'creativity' => 'Creative Problem Solving',
                'attention_detail' => 'Attention to Detail',
            ]
        ],
    ],

    'recommendation_levels' => [
        'highly_recommended' => [
            'label' => 'Highly Recommended',
            'description' => 'Outstanding candidate, strong fit for the program'
        ],
        'recommended' => [
            'label' => 'Recommended',
            'description' => 'Good candidate, meets program requirements'
        ],
        'conditional' => [
            'label' => 'Conditional',
            'description' => 'Candidate shows potential but has areas needing development'
        ],
        'not_recommended' => [
            'label' => 'Not Recommended',
            'description' => 'Candidate does not meet program requirements at this time'
        ],
    ],

    'overall_rating_levels' => [
        'excellent' => 'Excellent (90-100%)',
        'very_good' => 'Very Good (80-89%)',
        'good' => 'Good (70-79%)',
        'satisfactory' => 'Satisfactory (60-69%)',
        'needs_improvement' => 'Needs Improvement (Below 60%)',
    ],
];
