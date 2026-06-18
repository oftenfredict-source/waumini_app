@extends('layouts.church')

@section('title', $celebration->title)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-birthday-cake"></i> {{ $celebration->title }}</h1>
        <p>{{ $celebration->celebration_type->label() }} — {{ $celebration->celebration_date->format('M d, Y') }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.celebrations.index') }}">Celebrations</a></li>
        <li class="breadcrumb-item">Details</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="tile">
            <table class="table table-borderless table-sm">
                <tr><th width="180">Type</th><td><span class="badge badge-{{ $celebration->celebration_type->badgeClass() }}">{{ $celebration->celebration_type->label() }}</span></td></tr>
                <tr><th>Source</th><td>{{ $celebration->source->label() }}</td></tr>
                <tr><th>Status</th><td><span class="badge badge-{{ $celebration->status->badgeClass() }}">{{ $celebration->status->label() }}</span></td></tr>
                <tr><th>Celebration Date</th><td>{{ $celebration->celebration_date->format('l, M d, Y') }}</td></tr>
                @if($celebration->original_date)
                    <tr><th>Original Date</th><td>{{ $celebration->original_date->format('M d, Y') }}</td></tr>
                @endif
                @if($celebration->yearsCount())
                    <tr><th>Milestone</th><td>{{ $celebration->yearsCount() }} {{ $celebration->celebration_type === \App\Enums\CelebrationType::WeddingAnniversary ? 'years married' : 'years' }}</td></tr>
                @endif
                @if($celebration->wedding_type)
                    <tr><th>Wedding Type</th><td>{{ $celebration->wedding_type->label() }}</td></tr>
                @endif
                @if($celebration->member)
                    <tr><th>Member</th><td><a href="{{ route('church.members.show', $celebration->member) }}">{{ $celebration->member->full_name }}</a></td></tr>
                @endif
                <tr><th>Notes</th><td>{{ $celebration->notes ?? '—' }}</td></tr>
                <tr><th>Created</th><td>{{ $celebration->created_at->format('M d, Y H:i') }}@if($celebration->creator) by {{ $celebration->creator->name }}@endif</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <div class="tile">
            <h3 class="tile-title">Actions</h3>
            <a href="{{ route('church.celebrations.index') }}" class="btn btn-secondary btn-block mb-2">
                <i class="fa fa-arrow-left"></i> Back to Celebrations
            </a>
            @can('update', $celebration)
                <a href="{{ route('church.celebrations.edit', $celebration) }}" class="btn btn-primary btn-block mb-2">
                    <i class="fa fa-pencil"></i> Edit
                </a>
            @endcan
            @can('delete', $celebration)
                <form method="POST" action="{{ route('church.celebrations.destroy', $celebration) }}"
                    data-swal-confirm="Remove this celebration?" data-swal-delete>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block">
                        <i class="fa fa-trash"></i>
                        @if($celebration->source === \App\Enums\CelebrationSource::Auto)
                            Cancel Auto Entry
                        @else
                            Delete Celebration
                        @endif
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
