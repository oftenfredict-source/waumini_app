@extends('layouts.church')

@section('title', 'Record Asset')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-plus"></i> Record Asset</h1>
        <p>Add a new church asset to the register</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.assets.index') }}">Assets</a></li>
        <li class="breadcrumb-item">Record</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.assets.store') }}" enctype="multipart/form-data">
        @csrf
        @include('church.assets._form')
        <div class="tile-footer mt-4">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Asset</button>
            <a href="{{ route('church.assets.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
