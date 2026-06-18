@extends('layouts.church')

@section('title', 'Edit Branch')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Branch</h1>
        <p>{{ $branch->name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.branches.index') }}">Branches</a></li>
        <li class="breadcrumb-item">{{ $branch->code }}</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.branches.update', $branch) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('church.branches.partials.form', ['branch' => $branch])
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
        <a href="{{ route('church.branches.show', $branch) }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
