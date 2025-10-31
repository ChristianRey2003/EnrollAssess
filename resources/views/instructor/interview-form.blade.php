@extends('layouts.instructor')

@section('title', 'Interview Evaluation - ' . $applicant->full_name)

@php
    $pageTitle = 'Interview Evaluation';
    $pageSubtitle = $applicant->full_name . ' - ' . $applicant->application_no;
@endphp

@section('content')
@include('components.interview.evaluation-form', [
    'mode' => 'instructor',
    'form_action' => route('instructor.interview.submit', $applicant->applicant_id),
    'applicant' => $applicant,
    'interview' => $interview,
    'back_url' => route('instructor.applicants')
])
@endsection
