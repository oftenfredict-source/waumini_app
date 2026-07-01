@extends('layouts.church')

@section('title', $memberRequest->reference_number)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-envelope-open',
    'title' => $memberRequest->subject,
    'subtitle' => $memberRequest->reference_number . ' — ' . ($memberRequest->member?->full_name ?? ''),
    'breadcrumb' => [
        ['label' => __('common.dashboard'), 'route' => 'church.dashboard'],
        ['label' => __('menu.member_requests'), 'route' => 'church.member-requests.index'],
        ['label' => $memberRequest->reference_number],
    ],
])

<div class="row">
    <div class="col-lg-7">
        <div class="tile mb-3">
            <div class="mb-3">
                <span class="badge badge-{{ $memberRequest->status->badgeClass() }}">{{ $memberRequest->status->label() }}</span>
                <span class="badge badge-light">{{ $memberRequest->type->label() }}</span>
            </div>
            <table class="table table-borderless table-sm">
                <tr><th width="170">{{ __('common.member') }}</th><td>{{ $memberRequest->member?->full_name }} (<code>{{ $memberRequest->member?->member_number }}</code>)</td></tr>
                <tr><th>{{ __('common.phone') }}</th><td>{{ $memberRequest->member?->phone_number ?? '—' }}</td></tr>
                <tr><th>{{ __('pages.shared.assigned_leader') }}</th><td>{{ $memberRequest->assignedLeader?->member?->full_name ?? '—' }} — {{ $memberRequest->assignedLeader?->positionLabel() }}</td></tr>
                <tr><th>{{ __('pages.shared.submitted') }}</th><td>{{ $memberRequest->created_at?->format('M d, Y g:i A') }}</td></tr>
            </table>
            <hr>
            <h5>{{ __('pages.member_requests.request_details') }}</h5>
            @if($memberRequest->type === \App\Enums\MemberRequestType::BaptismRequest && !empty($memberRequest->request_meta['candidates']))
                <div class="mb-3">
                    <strong>{{ __('pages.member_requests.persons_baptism') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($memberRequest->request_meta['candidates'] as $candidate)
                            <li>
                                {{ $candidate['name'] ?? '—' }}
                                <span class="text-muted">({{ ($candidate['relationship'] ?? '') === 'self' ? __('pages.member_requests.candidate_member') : __('pages.member_requests.candidate_child') }})</span>
                                @if(!empty($candidate['date_of_birth']))
                                    <small class="text-muted">— {{ __('pages.member_requests.dob_label') }} {{ $candidate['date_of_birth'] }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if(!empty($memberRequest->request_meta['preferred_baptism_date']))
                        <p class="mt-2 mb-0"><strong>{{ __('pages.member_requests.preferred_date') }}</strong> {{ $memberRequest->request_meta['preferred_baptism_date'] }}</p>
                    @endif
                </div>
            @endif
            <p class="mb-0">{!! nl2br(e($memberRequest->description)) !!}</p>
        </div>

        @if($memberRequest->response)
            <div class="tile">
                <h3 class="tile-title">{{ __('pages.member_requests.previous_response') }}</h3>
                <p class="mb-0">{!! nl2br(e($memberRequest->response)) !!}</p>
                <small class="text-muted d-block mt-2">
                    {{ $memberRequest->responded_at?->format('M d, Y g:i A') }}
                    @if($memberRequest->responder) — {{ $memberRequest->responder->name }} @endif
                </small>
            </div>
        @endif

        @if($memberRequest->hasDownloadableCertificate())
            <div class="tile">
                <h3 class="tile-title"><i class="fa fa-certificate"></i> {{ __('pages.member_requests.certificate_title') }}</h3>
                <p class="mb-2 text-muted small">
                    {{ __('pages.member_requests.certificate_generated', [
                        'datetime' => $memberRequest->certificate_generated_at?->format('M d, Y g:i A') ?? __('pages.member_requests.certificate_generated_approval'),
                    ]) }}
                </p>
                <a href="{{ route('church.member-requests.certificate', $memberRequest) }}" class="btn btn-success">
                    <i class="fa fa-download"></i> {{ __('pages.member_requests.download_certificate_pdf') }}
                </a>
            </div>
        @endif
    </div>

    @can('respond', $memberRequest)
        <div class="col-lg-5">
            <div class="tile">
                <h3 class="tile-title">{{ __('pages.member_requests.update_request') }}</h3>
                @if($memberRequest->type->generatesCertificate())
                    <div class="alert alert-info py-2 small">
                        {!! __('pages.member_requests.certificate_approve_alert', ['type' => $memberRequest->type->label()]) !!}
                    </div>
                @endif
                <form method="POST" action="{{ route('church.member-requests.respond', $memberRequest) }}">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('common.status') }} <span class="text-danger">*</span></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $memberRequest->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>{{ __('pages.member_requests.response_to_member') }}</label>
                        <textarea name="response" rows="5" class="form-control @error('response') is-invalid @enderror"
                                  placeholder="{{ __('pages.member_requests.response_placeholder') }}">{{ old('response', $memberRequest->response) }}</textarea>
                        @error('response')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-save"></i> {{ __('pages.member_requests.save_update') }}
                    </button>
                </form>
            </div>
        </div>
    @endcan
</div>
@endsection
