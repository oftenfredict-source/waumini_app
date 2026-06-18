@extends('layouts.church')

@section('title', 'Add Celebration')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Add Celebration</h1>
        <p>Create a manual celebration entry</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.celebrations.index') }}">Celebrations</a></li>
        <li class="breadcrumb-item">Add</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.celebrations.store') }}">
        @csrf
        @include('church.celebrations._form')
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Celebration</button>
            <a href="{{ route('church.celebrations.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
