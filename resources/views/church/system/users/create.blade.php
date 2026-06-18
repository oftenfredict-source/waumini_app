@extends('layouts.church')

@section('title', 'Add Staff User')

@section('content')
@include('church.system.partials.nav')

<div class="app-title">
    <div>
        <h1><i class="fa fa-user-plus"></i> Add Staff User</h1>
        <p>Create a new church staff login</p>
    </div>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.system.users.store') }}">
        @csrf
        @include('church.system.users._form')
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create User</button>
        <a href="{{ route('church.system.users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
