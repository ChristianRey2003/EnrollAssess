{{-- 
    Reusable Modal Component
    
    Props:
    - id: Modal ID (required)
    - title: Modal title
    - size: sm, md, lg, xl (default: md)
    - closable: boolean (default: true)
    
    Slots:
    - header: Custom header content
    - footer: Custom footer content
    - default: Main content
--}}

@props([
    'id' => '',
    'title' => '',
    'size' => 'md',
    'closable' => true
])

@php
$sizeClasses = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl'
];
$modalClasses = 'modal-content ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<div id="{{ $id }}" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
    <div class="{{ $modalClasses }}" role="document">
        @if($title || isset($header) || $closable)
        <div class="modal-header">
            @isset($header)
                {{ $header }}
            @else
                <h3 id="{{ $id }}-title" class="modal-title">{{ $title }}</h3>
            @endisset
            
            @if($closable)
            <button type="button" 
                    class="modal-close" 
                    onclick="closeModal('{{ $id }}')"
                    aria-label="Close modal">
                <span aria-hidden="true">×</span>
            </button>
            @endif
        </div>
        @endif

        <div class="modal-body">
            {{ $slot }}
        </div>

        @isset($footer)
        <div class="modal-footer">
            {{ $footer }}
        </div>
        @endisset
    </div>
</div>