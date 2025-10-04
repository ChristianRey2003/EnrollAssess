@extends('layouts.instructor')

@section('title', 'Interview History')

@php
    $pageTitle = 'Interview History';
    $pageSubtitle = 'Review your completed interview evaluations and performance statistics';
@endphp

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full" style="background-color: #FFF8DC; color: #800020;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Completed</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_completed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Average Score</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($statistics['average_score'], 1) }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Recommended</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['recommended_count'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">This Month</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['this_month'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interview History Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Completed Interviews</h3>
        </div>
        
        @if($completedInterviews->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interview Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overall Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recommendation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($completedInterviews as $interview)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ substr($interview->applicant->full_name ?? 'A', 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $interview->applicant->full_name ?? 'N/A' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $interview->applicant->email_address ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $interview->interview_date ? $interview->interview_date->format('M d, Y g:i A') : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $interview->overall_score ?? 0 }}%
                                        </span>
                                        <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full" style="background-color: #800020; width: {{ $interview->overall_score ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $ratingClass = match($interview->overall_rating ?? '') {
                                            'excellent' => 'bg-green-100 text-green-800',
                                            'very_good' => 'text-blue-800',
                                            'good' => 'bg-yellow-100 text-yellow-800',
                                            'satisfactory' => 'bg-orange-100 text-orange-800',
                                            'needs_improvement' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $ratingClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $interview->overall_rating ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $recommendationClass = match($interview->recommendation ?? '') {
                                            'highly_recommended' => 'bg-green-100 text-green-800',
                                            'recommended' => 'text-blue-800',
                                            'conditional' => 'bg-yellow-100 text-yellow-800',
                                            'not_recommended' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $recommendationClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $interview->recommendation ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match($interview->applicant->status ?? '') {
                                            'admitted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'interview-completed' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst(str_replace('-', ' ', $interview->applicant->status ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="hover:text-blue-900" style="color: #800020;" onclick="viewInterviewDetails({{ $interview->interview_id }})">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $completedInterviews->links() }}
            </div>
        @else
            <div class="px-6 py-8 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No completed interviews yet</h3>
                <p class="text-gray-500">You haven't completed any interviews yet. Start by scheduling interviews with your assigned applicants.</p>
                <div class="mt-4">
                    <a href="{{ route('instructor.applicants') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #800020; hover:background-color: #5c0017;">
                        View Assigned Applicants
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function viewInterviewDetails(interviewId) {
        // This would typically open a modal or navigate to a detail page
        alert('Interview details view would open here for interview ID: ' + interviewId);
    }
</script>
@endpush