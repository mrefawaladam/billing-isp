@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'placeholder' => null,
    'help' => null,
    'errors' => null
])

@php
    $errorBag = $errors ?? (isset($errors) ? $errors : null);
    $hasError = false;
    $errorMessage = null;

    if ($errorBag) {
        if (is_object($errorBag) && method_exists($errorBag, 'has')) {
            $hasError = $errorBag->has($name);
            $errorMessage = $hasError ? $errorBag->first($name) : null;
        } elseif (is_array($errorBag) && isset($errorBag[$name])) {
            $hasError = true;
            $errorMessage = is_array($errorBag[$name]) ? $errorBag[$name][0] : $errorBag[$name];
        }
    }

    $inputValue = $value ?? old($name, '');
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>
    <input
        type="{{ $type }}"
        class="form-control @if($hasError) is-invalid @endif"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $inputValue }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        {{ $attributes }}
    >
    @if($hasError)
        <div class="invalid-feedback d-block">{{ $errorMessage }}</div>
    @endif
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

