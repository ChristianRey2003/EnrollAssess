@extends('layouts.admin')

@section('title', 'Conduct Interview - ' . $applicant->full_name)

@php
    $pageTitle = 'Conduct Interview';
    $pageSubtitle = $applicant->full_name . ' - ' . $applicant->application_no;
@endphp

@section('content')
@if($interview->claimed_by && $interview->claimed_by !== auth()->id() && $interview->claimed_at && $interview->claimed_at->gt(now()->subHour()))
    <div style="background: #FEF3C7; border: 2px solid #F59E0B; border-radius: 6px; padding: 12px 16px; color: #92400E; font-size: 0.875rem; margin-bottom: 20px;">
        This interview is currently being conducted by {{ \App\Models\User::find($interview->claimed_by)->full_name ?? 'another evaluator' }}.
        Started {{ $interview->claimed_at->diffForHumans() }}.
    </div>
@endif

@include('components.interview.evaluation-form', [
    'mode' => 'admin',
    'form_action' => route('admin.interviews.conduct.submit', $interview->interview_id),
    'applicant' => $applicant,
    'interview' => $interview,
    'back_url' => route('admin.interviews.index')
])
@endsection
