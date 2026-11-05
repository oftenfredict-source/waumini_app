@php
    $currencies = config('currencies', []);
    $selected = old($fieldName, $fieldValue ?? 'TZS');
@endphp
<select name="{{ $fieldName }}" id="{{ $fieldId ?? $fieldName }}" class="form-select" @if(!empty($required)) required @endif>
    @foreach($currencies as $code => $label)
        <option value="{{ $code }}" @selected($selected === $code)>{{ $label }}</option>
    @endforeach
</select>
