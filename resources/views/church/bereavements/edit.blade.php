@extends('layouts.church')

@section('title', 'Edit Bereavement')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Bereavement</h1>
        <p>{{ $event->deceased_name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.bereavements.index') }}">Bereavements</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.bereavements.update', $event) }}">
        @csrf
        @method('PUT')
        @include('church.bereavements._form', [
            'event' => $event,
            'members' => $members,
        ])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Bereavement</button>
            <a href="{{ route('church.bereavements.show', $event) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
