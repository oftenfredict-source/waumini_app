@extends('layouts.church')

@section('title', 'Add Department')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Add Department</h1>
        <p>Create a new church department or ministry</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.departments.index') }}">Departments</a></li>
        <li class="breadcrumb-item">Add</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.departments.store') }}">
        @csrf
        @include('church.departments._form')
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Department</button>
            <a href="{{ route('church.departments.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
