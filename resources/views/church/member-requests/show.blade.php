@extends('layouts.church')

@section('title', $memberRequest->reference_number)

@section('content')
<div class="app-title">
    <div>
        <h1><i class="fa fa-envelope-open"></i> {{ $memberRequest->subject }}</h1>
        <p><code>{{ $memberRequest->reference_number }}</code> — {{ $memberRequest->member?->full_name }}</p>
    </div>
    <ul class="app-breadcrumb breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('church.member-requests.index') }}">Member Requests</a></li>
        <li class="breadcrumb-item">{{ $memberRequest->reference_number }}</li>
    </ul>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="tile mb-3">
            <div class="mb-3">
                <span class="badge badge-{{ $memberRequest->status->badgeClass() }}">{{ $memberRequest->status->label() }}</span>
                <span class="badge badge-light">{{ $memberRequest->type->label() }}</span>
            </div>
            <table class="table table-borderless table-sm">
                <tr><th width="170">Member</th><td>{{ $memberRequest->member?->full_name }} (<code>{{ $memberRequest->member?->member_number }}</code>)</td></tr>
                <tr><th>Phone</th><td>{{ $memberRequest->member?->phone_number ?? '—' }}</td></tr>
                <tr><th>Assigned Leader</th><td>{{ $memberRequest->assignedLeader?->member?->full_name ?? '—' }} — {{ $memberRequest->assignedLeader?->positionLabel() }}</td></tr>
                <tr><th>Submitted</th><td>{{ $memberRequest->created_at?->format('M d, Y g:i A') }}</td></tr>
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
                                <span class="text-muted">({{ ($candidate['relationship'] ?? '') === 'self' ? 'Member' : 'Child' }})</span>
                                @if(!empty($candidate['date_of_birth']))
                                    <small class="text-muted">— DOB: {{ $candidate['date_of_birth'] }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if(!empty($memberRequest->request_meta['preferred_baptism_date']))
                        <p class="mt-2 mb-0"><strong>Preferred date:</strong> {{ $memberRequest->request_meta['preferred_baptism_date'] }}</p>
                    @endif
                </div>
            @endif
            <p class="mb-0">{!! nl2br(e($memberRequest->description)) !!}</p>
        </div>

        @if($memberRequest->response)
            <div class="tile">
                <h3 class="tile-title">Previous Response</h3>
                <p class="mb-0">{!! nl2br(e($memberRequest->response)) !!}</p>
                <small class="text-muted d-block mt-2">
                    {{ $memberRequest->responded_at?->format('M d, Y g:i A') }}
                    @if($memberRequest->responder) — {{ $memberRequest->responder->name }} @endif
                </small>
            </div>
        @endif

        @if($memberRequest->hasDownloadableCertificate())
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-certificate"></i> Certificate</h3>
                <p class="mb-2 text-muted small">
                    Generated {{ $memberRequest->certificate_generated_at?->format('M d, Y g:i A') ?? 'on approval' }}
                </p>
                <a href="{{ route('church.member-requests.certificate', $memberRequest) }}" class="btn btn-success">
                    <i class="fa fa-download"></i> Download Certificate (PDF)
                </a>
            </div>
        @endif
    </div>

    @can('respond', $memberRequest)
        <div class="col-lg-5">
            <div class="tile">
                <h3 class="tile-title">Update Request</h3>
                @if($memberRequest->type->generatesCertificate())
                    <div class="alert alert-info py-2 small">
                        Approving or marking this request as <strong>Completed</strong> will automatically generate a
                        downloadable {{ $memberRequest->type->label() }} PDF for the member.
                    </div>
                @endif
                <form method="POST" action="{{ route('church.member-requests.respond', $memberRequest) }}">
                    @csrf
                    <div class="form-group">
                        <label>Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $memberRequest->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>Response to Member</label>
                        <textarea name="response" rows="5" class="form-control @error('response') is-invalid @enderror"
                                  placeholder="Explain the decision or next steps for the member.">{{ old('response', $memberRequest->response) }}</textarea>
                        @error('response')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-save"></i> Save Update
                    </button>
                </form>
            </div>
        </div>
    @endcan
</div>
@endsection
