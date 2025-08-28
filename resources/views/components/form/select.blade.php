{{--
    Reusable Form Select Component
    
    Props:
    - name: Select name attribute (required)
    - label: Label text
    - options: Array of options or collection
    - selected: Selected value
    - placeholder: Placeholder option text
    - required: boolean (default: false)
    - disabled: boolean (default: false)
    - error: Error message to display
    - help: Help text to display
    - id: Custom ID (defaults to name)
--}}

@props([
    'name' => '',
    'label' => '',
    'options' => [],
    'selected' => '',
    'placeholder' => 'Select an option',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => '',
    'id' => null
])

@php
$inputId = $id ?? $name;
$hasError = !empty($error);
$classes = collect([
    'form-control',
    'form-select',
    $hasError ? 'is-invalid' : ''
])->filter()->implode(' ');
@endphp

<div class="form-group">
    @if($label)
    <label for="{{ $inputId }}" class="form-label {{ $required ? 'required' : '' }}">
        {{ $label }}
    </label>
    @endif
    
    <select 
        id="{{ $inputId }}"
        name="{{ $name }}"
        class="{{ $classes }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($hasError) aria-invalid="true" aria-describedby="{{ $inputId }}-error" @endif
        @if($help) aria-describedby="{{ $inputId }}-help" @endif
        {{ $attributes }}
    >
        @if($placeholder)
        <option value="">{{ $placeholder }}</option>
        @endif
        
        @if(is_array($options))
            @foreach($options as $value => $text)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
                {{ $text }}
            </option>
            @endforeach
        @else
            @foreach($options as $option)
                @if(is_object($option))
                <option value="{{ $option->id ?? $option->value }}" 
                        {{ $selected == ($option->id ?? $option->value) ? 'selected' : '' }}>
                    {{ $option->name ?? $option->title ?? $option->text ?? $option->label }}
                </option>
                @else
                <option value="{{ $option }}" {{ $selected == $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
                @endif
            @endforeach
        @endif
    </select>
    
    @if($help)
    <div id="{{ $inputId }}-help" class="form-help">{{ $help }}</div>
    @endif
    
    @if($hasError)
    <div id="{{ $inputId }}-error" class="invalid-feedback" role="alert">
        {{ $error }}
    </div>
    @endif
</div>
