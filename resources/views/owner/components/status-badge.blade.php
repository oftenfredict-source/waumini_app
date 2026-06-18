@php
    $status = $status instanceof \App\Enums\ChurchStatus ? $status : \App\Enums\ChurchStatus::tryFrom($status);
@endphp
@if($status)
    <span class="badge badge-{{ $status->badgeClass() }}">{{ $status->label() }}</span>
@else
    <span class="badge badge-secondary">{{ $status }}</span>
@endif
