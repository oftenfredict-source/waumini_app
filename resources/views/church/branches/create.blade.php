@extends('layouts.church')

@section('title', 'Add Branch')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Add Branch</h1>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.branches.index') }}">Branches</a></li>
        <li class="breadcrumb-item">Add</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.branches.store') }}" enctype="multipart/form-data">
        @csrf
        @include('church.branches.partials.form')
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Branch</button>
        <a href="{{ route('church.branches.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
