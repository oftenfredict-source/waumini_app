@extends('layouts.church')

@section('title', 'Create Bereavement')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Create Bereavement</h1>
        <p>Record a bereavement and open the contribution period</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.bereavements.index') }}">Bereavements</a></li>
        <li class="breadcrumb-item">Create</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.bereavements.store') }}">
        @csrf
        @include('church.bereavements._form', ['members' => $members])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Bereavement</button>
            <a href="{{ route('church.bereavements.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('affected_member_id');
    const nameInput = document.querySelector('input[name="deceased_name"]');
    if (!select || !nameInput) return;

    select.addEventListener('change', function () {
        const option = select.options[select.selectedIndex];
        if (option.value && !nameInput.value.trim()) {
            nameInput.value = option.dataset.name || '';
        }
    });
});
</script>
@endpush
