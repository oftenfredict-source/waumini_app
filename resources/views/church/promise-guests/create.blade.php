@extends('layouts.church')

@section('title', 'Add Guest')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Add Guest</h1>
        <p>Register a promised or temporary guest</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.promise-guests.index') }}">Guests</a></li>
        <li class="breadcrumb-item">Add</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.promise-guests.store') }}">
        @csrf
        @include('church.promise-guests._form')
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Guest</button>
            <a href="{{ route('church.promise-guests.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
