@extends('layouts.church')

@section('title', 'Edit Staff User')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-edit"></i> Edit Staff User</h1>
        <p>{{ $user->name }}</p>
    </div>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.system.users.update', $user) }}">
        @csrf
        @method('PUT')
        @include('church.system.users._form', ['user' => $user])
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
        <a href="{{ route('church.system.users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
