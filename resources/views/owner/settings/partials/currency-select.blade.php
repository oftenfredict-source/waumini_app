@php
    $currencies = config('currencies', []);
    $selected = old($fieldName, $fieldValue ?? 'USD');
@endphp
<select name="{{ $fieldName }}" id="{{ $fieldId ?? $fieldName }}" class="form-control" @if(!empty($required)) required @endif>
    @foreach($currencies as $code => $label)
        <option value="{{ $code }}" @if($selected === $code) selected @endif>{{ $label }}</option>
    @endforeach
</select>
