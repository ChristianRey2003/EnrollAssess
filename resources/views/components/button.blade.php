{{--
    Reusable Button Component
    
    Props:
    - type: submit, button, reset (default: button)
    - variant: primary, secondary, success, danger, warning (default: primary)
    - size: sm, md, lg (default: md)
    - disabled: boolean (default: false)
    - loading: boolean (default: false)
    - href: URL for link buttons
    - onclick: JavaScript handler
    - icon: Icon to display (emoji or HTML)
    - iconPosition: left, right (default: left)
--}}

@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'loading' => false,
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold tracking-widest uppercase border border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150';

    $variantClasses = [
        'primary' => 'bg-primary text-white hover:bg-primary-hover focus:ring-primary-focus',
        'secondary' => 'bg-secondary text-dark border-gray-300 hover:bg-secondary-hover focus:ring-secondary-focus',
        'danger' => 'bg-danger text-white hover:bg-danger-hover focus:ring-danger-focus',
        'success' => 'bg-success text-white hover:bg-success-hover focus:ring-success-focus',
        'warning' => 'bg-warning text-dark hover:bg-warning-hover focus:ring-warning-focus',
    ];

    $sizeClasses = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $classes = collect([
        $baseClasses,
        $variantClasses[$variant] ?? $variantClasses['primary'],
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $disabled || $loading ? 'opacity-25 cursor-not-allowed' : '',
    ])->filter()->implode(' ');

    $isDisabled = $disabled || $loading;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'aria-disabled' => $isDisabled ? 'true' : 'false', 'role' => 'button']) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @if($isDisabled) disabled @endif>
        {{ $slot }}
    </button>
@endif
