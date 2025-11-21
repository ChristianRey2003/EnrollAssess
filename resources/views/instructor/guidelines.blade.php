@extends('layouts.instructor')

@section('title', 'Interview Guidelines')

@php
    $pageTitle = 'Interview Guidelines';
    $pageSubtitle = 'Best practices and evaluation criteria for conducting effective interviews';
@endphp

@push('styles')
<link href="{{ asset('css/admin/interviews.css') }}" rel="stylesheet">
<style>
    .main-content {
        padding: 20px !important;
    }

    .guidelines-container {
        width: 100%;
        max-width: none;
        margin: 0;
    }

    .content-card {
        background: #FFFFFF;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #E5E7EB;
        margin-bottom: 24px;
    }

    .content-header {
        padding: 20px 24px;
        background: #F9FAFB;
        border-bottom: 1px solid #E5E7EB;
    }

    .content-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1F2937;
    }

    .content-body {
        padding: 24px;
    }

    .criteria-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }

    .criteria-item {
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 16px;
    }

    .criteria-item h4 {
        margin: 0 0 8px 0;
        font-size: 14px;
        font-weight: 600;
        color: #1F2937;
    }

    .criteria-item p {
        margin: 0;
        font-size: 13px;
        color: #6B7280;
        line-height: 1.5;
    }

    .criteria-item .score-info {
        margin-top: 8px;
        font-size: 12px;
        color: #800020;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .criteria-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="guidelines-container">
    <!-- BSIT Evaluation Criteria -->
    <div class="content-card">
        <div class="content-header">
            <h2>BSIT Evaluation Criteria</h2>
        </div>
        <div class="content-body">
            <p style="font-size: 13px; color: #6B7280; margin-bottom: 20px;">
                Each criterion is scored from 1-5 points (Needs Improvement to Excellent). Total possible score: 80 points (8 criteria × 10 points each).
            </p>
            
            <div class="criteria-grid">
                <div class="criteria-item">
                    <h4>1. Communication Skills</h4>
                    <p>Ability to express ideas clearly, confidently, and with proper grammar. Assess verbal communication, clarity, and articulation.</p>
                    <div class="score-info">Score: 0-10 points</div>
                </div>
                
                <div class="criteria-item">
                    <h4>2. Motivation and Interest</h4>
                    <p>Level of motivation and clear, relevant goals related to IT. Assess passion, career alignment, and commitment to the program.</p>
                    <div class="score-info">Score: 0-10 points</div>
                </div>
                
                <div class="criteria-item">
                    <h4>3. Problem-solving Attitude</h4>
                    <p>Demonstrates analytical skills and eagerness to solve problems. Assess critical thinking, approach to challenges, and logical reasoning.</p>
                    <div class="score-info">Score: 0-10 points</div>
                </div>
                
                <div class="criteria-item">
                    <h4>4. Understanding of the Program</h4>
                    <p>Clear understanding of BSIT curriculum and expectations. Assess knowledge of program structure, courses, and career outcomes.</p>
                    <div class="score-info">Score: 0-10 points</div>
                </div>
                
                <div class="criteria-item">
                    <h4>5. Personality and Attitude</h4>
                    <p>Pleasant attitude, respectful, and shows potential. Assess professionalism, interpersonal skills, and overall demeanor.</p>
                    <div class="score-info">Score: 0-10 points</div>
                </div>
                
                <div class="criteria-item">
                    <h4>6. IT Exposure/Background</h4>
                    <p>Background or exposure to IT tools and concepts. Assess prior experience, technical familiarity, and foundational knowledge.</p>
                    <div class="score-info">Score: 0-10 points</div>
                </div>
                
                <div class="criteria-item">
                    <h4>7. Willingness to Learn</h4>
                    <p>Eagerness and commitment to learning new things. Assess openness to feedback, growth mindset, and learning attitude.</p>
                    <div class="score-info">Score: 0-10 points</div>
                </div>
                
                <div class="criteria-item">
                    <h4>8. Overall Impression</h4>
                    <p>Comprehensive assessment of candidate suitability. Consider all factors together to form final evaluation.</p>
                    <div class="score-info">Score: 0-10 points</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
