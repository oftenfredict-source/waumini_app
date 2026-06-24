@php
    $name = $name ?? 'password';
    $inputId = $id ?? $name;
    $placeholder = $placeholder ?? '';
    $required = $required ?? true;
    $invalid = $invalid ?? false;
@endphp

<div class="password-toggle-field">
    <input
        id="{{ $inputId }}"
        class="form-control @if($invalid) is-invalid @endif"
        type="password"
        name="{{ $name }}"
        @if($placeholder !== '') placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
    >
    <button type="button" class="password-toggle-btn" data-password-target="{{ $inputId }}" aria-label="Show password" title="Show password">
        <i class="fa fa-eye" aria-hidden="true"></i>
    </button>
</div>

@once
@push('styles')
<style>
    .password-toggle-field {
        position: relative;
    }
    .password-toggle-field .form-control {
        padding-right: 2.75rem;
    }
    .password-toggle-btn {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: 2.5rem;
        border: 0;
        background: transparent;
        color: #6c757d;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .password-toggle-btn:hover,
    .password-toggle-btn:focus {
        color: var(--waumini-brand, #940000);
        outline: none;
    }
</style>
@endpush
@push('scripts')
<script>
(function () {
    document.querySelectorAll('.password-toggle-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.getAttribute('data-password-target'));
            if (!input) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';

            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
            }

            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            button.setAttribute('title', show ? 'Hide password' : 'Show password');
        });
    });
})();
</script>
@endpush
@endonce
