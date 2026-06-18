@extends('layouts.church')

@section('title', 'Edit Department')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Department</h1>
        <p>Update {{ $department->name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.departments.index') }}">Departments</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.departments.show', $department) }}">{{ $department->name }}</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.departments.update', $department) }}">
        @csrf
        @method('PUT')
        @include('church.departments._form', ['department' => $department])
        <div class="tile-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Department</button>
            <a href="{{ route('church.departments.show', $department) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
