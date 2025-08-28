{{--
    Status Badge Component
    
    Props:
    - status: The status value (required)
    - type: The type of status (default: 'application')
    - class: Additional CSS classes
--}}

@props([
    'status' => '',
    'type' => 'application',
    'class' => ''
])

@php
    // Normalize status string for CSS class
    $normalizedStatus = strtolower(str_replace([' ', '_'], '-', $status));
    
    // Determine the CSS class based on status and type
    $statusClass = 'status-' . $normalizedStatus;
    
    // Format display text
    $displayText = ucfirst(str_replace(['-', '_'], ' ', $status));
    
    // Build final CSS classes
    $classes = collect([
        'status-badge',
        $statusClass,
        $class
    ])->filter()->implode(' ');
@endphp

<span class="{{ $classes }}" title="{{ $displayText }}">
    {{ $displayText }}
</span>