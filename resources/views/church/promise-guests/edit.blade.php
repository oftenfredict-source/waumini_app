@extends('layouts.church')

@section('title', 'Edit Guest')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Guest</h1>
        <p>{{ $guest->name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.promise-guests.index') }}">Guests</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.promise-guests.update', $guest) }}">
        @csrf
        @method('PUT')
        @include('church.promise-guests._form', ['guest' => $guest])
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Guest</button>
            <a href="{{ route('church.promise-guests.show', $guest) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
