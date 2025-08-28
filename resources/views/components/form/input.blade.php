{{--
    Reusable Form Input Component
    
    Props:
    - name: Input name attribute (required)
    - type: Input type (default: text)
    - label: Label text
    - placeholder: Placeholder text
    - value: Default value
    - required: boolean (default: false)
    - disabled: boolean (default: false)
    - error: Error message to display
    - help: Help text to display
    - id: Custom ID (defaults to name)
--}}

@props([
    'name' => '',
    'type' => 'text',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => '',
    'id' => null
])

@php
    $inputId = $id ?? $name;
    $hasError = !empty($error);
    $baseClasses = 'block w-full border-gray-300 focus:border-primary-focus focus:ring-primary-focus rounded-md shadow-sm';
    $errorClasses = 'border-danger focus:border-danger-focus focus:ring-danger-focus';
    $classes = $baseClasses . ($hasError ? ' ' . $errorClasses : '');
@endphp

@if($label)
    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 {{ $required ? 'after:content-[\'*\'] after:ml-0.5 after:text-red-500' : '' }}">
        {{ $label }}
    </label>
@endif

<input 
    type="{{ $type }}"
    id="{{ $inputId }}"
    name="{{ $name }}"
    class="{{ $classes }}"
    placeholder="{{ $placeholder }}"
    value="{{ $value }}"
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($hasError) aria-invalid="true" @endif
    {{ $attributes }}
>

@if($help && !$hasError)
    <p class="mt-2 text-sm text-gray-500">{{ $help }}</p>
@endif

@if($hasError)
    <p class="mt-2 text-sm text-danger" role="alert">{{ $error }}</p>
@endif
