@extends('layouts.church')

@section('title', 'Edit Celebration')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Celebration</h1>
        <p>{{ $celebration->title }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.celebrations.index') }}">Celebrations</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.celebrations.update', $celebration) }}">
        @csrf
        @method('PUT')
        @include('church.celebrations._form', ['celebration' => $celebration])
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
            <a href="{{ route('church.celebrations.show', $celebration) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
