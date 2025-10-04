@extends('layouts.instructor')

@section('title', 'Interview Guidelines')

@php
    $pageTitle = 'Interview Guidelines';
    $pageSubtitle = 'Best practices and evaluation criteria for conducting effective interviews';
@endphp

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Guidelines Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Guidelines -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Pre-Interview Preparation -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Pre-Interview Preparation</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1" style="background-color: #FFF8DC;">
                            <span class="text-sm font-medium" style="color: #800020;">1</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Review Applicant Portfolio</h3>
                            <p class="text-gray-600 mt-1">Thoroughly examine the applicant's exam results, educational background, and any previous evaluations before the interview.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1" style="background-color: #FFF8DC;">
                            <span class="text-sm font-medium" style="color: #800020;">2</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Prepare Technical Questions</h3>
                            <p class="text-gray-600 mt-1">Develop relevant technical questions based on the applicant's background and the program requirements.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mr-3 mt-1" style="background-color: #FFF8DC;">
                            <span class="text-sm font-medium" style="color: #800020;">3</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Set Up Interview Environment</h3>
                            <p class="text-gray-600 mt-1">Ensure a quiet, professional environment conducive to open communication and focused discussion.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluation Criteria -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Evaluation Criteria</h2>
                <div class="space-y-6">
                    <!-- Technical Skills -->
                    <div>
                        <h3 class="font-medium text-gray-900 mb-3">Technical Skills (40 points)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Programming Knowledge</h4>
                                <p class="text-sm text-gray-600 mt-1">Understanding of programming concepts, syntax, and problem-solving approaches</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Problem Solving</h4>
                                <p class="text-sm text-gray-600 mt-1">Ability to analyze problems, break them down, and develop logical solutions</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Algorithm Understanding</h4>
                                <p class="text-sm text-gray-600 mt-1">Knowledge of algorithms, data structures, and computational thinking</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">System Design</h4>
                                <p class="text-sm text-gray-600 mt-1">Ability to design software systems and understand architecture principles</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                        </div>
                    </div>

                    <!-- Communication Skills -->
                    <div>
                        <h3 class="font-medium text-gray-900 mb-3">Communication Skills (30 points)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Clarity of Expression</h4>
                                <p class="text-sm text-gray-600 mt-1">Ability to explain technical concepts clearly and understandably</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Active Listening</h4>
                                <p class="text-sm text-gray-600 mt-1">Demonstrates attentive listening and responds appropriately to questions</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Confidence</h4>
                                <p class="text-sm text-gray-600 mt-1">Presents ideas with confidence and maintains professional demeanor</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytical Thinking -->
                    <div>
                        <h3 class="font-medium text-gray-900 mb-3">Analytical Thinking (30 points)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Critical Thinking</h4>
                                <p class="text-sm text-gray-600 mt-1">Ability to evaluate information, identify assumptions, and draw logical conclusions</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Creativity</h4>
                                <p class="text-sm text-gray-600 mt-1">Demonstrates innovative thinking and ability to generate unique solutions</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900">Attention to Detail</h4>
                                <p class="text-sm text-gray-600 mt-1">Notices important details and considers edge cases in problem-solving</p>
                                <div class="mt-2 text-xs text-gray-500">Score: 0-10 points</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interview Process -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Interview Process</h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <span class="text-green-600 text-sm font-medium">1</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Introduction (5 minutes)</h3>
                            <p class="text-gray-600 mt-1">Welcome the applicant, explain the interview structure, and establish rapport.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <span class="text-green-600 text-sm font-medium">2</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Technical Assessment (20-25 minutes)</h3>
                            <p class="text-gray-600 mt-1">Evaluate technical knowledge through coding problems, system design questions, and technical discussions.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <span class="text-green-600 text-sm font-medium">3</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Communication & Problem-Solving (15-20 minutes)</h3>
                            <p class="text-gray-600 mt-1">Assess communication skills, analytical thinking, and problem-solving approach.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1">
                            <span class="text-green-600 text-sm font-medium">4</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Q&A & Closing (5-10 minutes)</h3>
                            <p class="text-gray-600 mt-1">Allow applicant questions, provide feedback, and conclude professionally.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Scoring Guide -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Scoring Guide</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">90-100%</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">80-89%</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FFF8DC; color: #800020;">Very Good</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">70-79%</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Good</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">60-69%</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Satisfactory</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">&lt;60%</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Needs Improvement</span>
                    </div>
                </div>
            </div>

            <!-- Recommendation Levels -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommendation Levels</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Highly Recommended</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Strong candidate</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Recommended</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" style="background-color: #FFF8DC; color: #800020;">Good candidate</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Conditional</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">With reservations</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Not Recommended</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Not suitable</span>
                    </div>
                </div>
            </div>

            <!-- Best Practices -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Best Practices</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <span class="mr-2" style="color: #800020;">•</span>
                        Maintain a professional and welcoming atmosphere
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2" style="color: #800020;">•</span>
                        Ask open-ended questions to encourage detailed responses
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2" style="color: #800020;">•</span>
                        Provide constructive feedback when appropriate
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2" style="color: #800020;">•</span>
                        Document specific examples and observations
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2" style="color: #800020;">•</span>
                        Be consistent in evaluation criteria across interviews
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2" style="color: #800020;">•</span>
                        Submit evaluations promptly after completion
                    </li>
                </ul>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('instructor.applicants') }}" class="block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #800020; hover:background-color: #5c0017;">
                        View Assigned Applicants
                    </a>
                    <a href="{{ route('instructor.schedule') }}" class="block w-full text-center px-4 py-2 border text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" style="border-color: #E9ECEF;">
                        Manage Schedule
                    </a>
                    <a href="{{ route('instructor.interview-history') }}" class="block w-full text-center px-4 py-2 border text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" style="border-color: #E9ECEF;">
                        View Interview History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection