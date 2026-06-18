@extends('layouts.church')

@section('title', $memberRequest->reference_number)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-envelope"></i> {{ $memberRequest->subject }}</h1>
        <p>Reference <code>{{ $memberRequest->reference_number }}</code></p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.member.requests.index') }}">My Requests</a></li>
        <li class="breadcrumb-item">{{ $memberRequest->reference_number }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <div class="mb-3">
                <span class="badge badge-{{ $memberRequest->status->badgeClass() }}">{{ $memberRequest->status->label() }}</span>
                <span class="badge badge-light">{{ $memberRequest->type->label() }}</span>
            </div>
            <table class="table table-borderless table-sm">
                <tr><th width="160">Submitted</th><td>{{ $memberRequest->created_at?->format('M d, Y g:i A') }}</td></tr>
                <tr><th>Assigned Leader</th><td>{{ $memberRequest->assignedLeader?->member?->full_name ?? '—' }} ({{ $memberRequest->assignedLeader?->positionLabel() }})</td></tr>
            </table>
            <hr>
            <h5>Request Details</h5>
            @if($memberRequest->type === \App\Enums\MemberRequestType::BaptismRequest && !empty($memberRequest->request_meta['candidates']))
                <div class="mb-3">
                    <strong>Person(s) for baptism:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($memberRequest->request_meta['candidates'] as $candidate)
                            <li>
                                {{ $candidate['name'] ?? '—' }}
                                <span class="text-muted">({{ ($candidate['relationship'] ?? '') === 'self' ? 'You' : 'Child' }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p class="mb-0">{!! nl2br(e($memberRequest->description)) !!}</p>
        </div>

        @if($memberRequest->response)
            <div class="tile">
                <h3 class="tile-title">Leader Response</h3>
                <p class="mb-2">{!! nl2br(e($memberRequest->response)) !!}</p>
                <small class="text-muted">
                    Updated {{ $memberRequest->responded_at?->format('M d, Y g:i A') }}
                    @if($memberRequest->responder) by {{ $memberRequest->responder->name }} @endif
                </small>
            </div>
        @endif

        @if($memberRequest->hasDownloadableCertificate())
            <div class="tile border-success">
                <h3 class="tile-title text-success"><i class="fa fa-certificate"></i> Your Certificate is Ready</h3>
                <p class="mb-3">Your {{ $memberRequest->type->label() }} has been approved. You can download the official PDF document below.</p>
                <a href="{{ route('church.member.requests.certificate', $memberRequest) }}" class="btn btn-success">
                    <i class="fa fa-download"></i> Download Certificate (PDF)
                </a>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="tile">
            <h3 class="tile-title">Status Guide</h3>
            <ul class="small mb-0 pl-3">
                <li><strong>Pending</strong> — waiting for the leader to review</li>
                <li><strong>In Review</strong> — leader is processing your request</li>
                <li><strong>Approved / Completed</strong> — request accepted or fulfilled</li>
                <li><strong>Rejected</strong> — request could not be approved</li>
            </ul>
        </div>
    </div>
</div>

<a href="{{ route('church.member.requests.index') }}" class="btn btn-secondary">
    <i class="fa fa-arrow-left"></i> Back to My Requests
</a>
@endsection
