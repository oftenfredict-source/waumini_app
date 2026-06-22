@extends('layouts.church')

@section('title', 'Edit Asset')

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-pencil"></i> Edit Asset</h1>
        <p>{{ $asset->name }} — <code>{{ $asset->asset_tag }}</code></p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.assets.index') }}">Assets</a></li>
        <li class="breadcrumb-item"><a href="{{ route('church.assets.show', $asset) }}">{{ $asset->name }}</a></li>
        <li class="breadcrumb-item">Edit</li>
    </ul>
</div>

<div class="tile">
    <form method="POST" action="{{ route('church.assets.update', $asset) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('church.assets._form')
        <div class="tile-footer mt-4">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
            <a href="{{ route('church.assets.show', $asset) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
