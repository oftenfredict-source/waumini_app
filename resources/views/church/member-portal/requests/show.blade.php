@extends('layouts.church')

@section('title', $memberRequest->reference_number)

@section('content')
@include('partials.page-header', [
    'icon' => 'fa fa-envelope',
    'title' => $memberRequest->subject,
    'subtitle' => __('pages.member_portal_requests.reference_subtitle', ['code' => $memberRequest->reference_number]),
    'breadcrumb' => [
        ['label' => __('pages.member_portal_requests.title'), 'route' => 'church.member.requests.index'],
        ['label' => $memberRequest->reference_number],
    ],
])

<div class="row">
    <div class="col-lg-8">
        <div class="tile mb-3">
            <div class="mb-3">
                <span class="badge badge-{{ $memberRequest->status->badgeClass() }}">{{ $memberRequest->status->label() }}</span>
                <span class="badge badge-light">{{ $memberRequest->type->label() }}</span>
            </div>
            <table class="table table-borderless table-sm">
                <tr><th width="160">{{ __('pages.shared.submitted') }}</th><td>{{ $memberRequest->created_at?->format('M d, Y g:i A') }}</td></tr>
                <tr><th>{{ __('pages.shared.assigned_leader') }}</th><td>{{ $memberRequest->assignedLeader?->member?->full_name ?? '—' }} ({{ $memberRequest->assignedLeader?->positionLabel() }})</td></tr>
            </table>
            <hr>
            <h5>{{ __('pages.member_portal_requests.request_details') }}</h5>
            @if($memberRequest->type === \App\Enums\MemberRequestType::BaptismRequest && !empty($memberRequest->request_meta['candidates']))
                <div class="mb-3">
                    <strong>{{ __('pages.member_portal_requests.persons_baptism') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($memberRequest->request_meta['candidates'] as $candidate)
                            <li>
                                {{ $candidate['name'] ?? '—' }}
                                <span class="text-muted">({{ ($candidate['relationship'] ?? '') === 'self' ? __('pages.member_portal_requests.candidate_self') : __('pages.member_portal_requests.candidate_child') }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p class="mb-0">{!! nl2br(e($memberRequest->description)) !!}</p>
        </div>

        @if($memberRequest->response)
            <div class="tile">
                <h3 class="tile-title">{{ __('pages.member_portal_requests.leader_response') }}</h3>
                <p class="mb-2">{!! nl2br(e($memberRequest->response)) !!}</p>
                <small class="text-muted">
                    {{ __('pages.member_portal_requests.updated_at', ['datetime' => $memberRequest->responded_at?->format('M d, Y g:i A')]) }}
                    @if($memberRequest->responder) {{ __('pages.member_portal_requests.updated_by', ['name' => $memberRequest->responder->name]) }} @endif
                </small>
            </div>
        @endif

        @if($memberRequest->hasDownloadableCertificate())
            <div class="tile border-success">
                <h3 class="tile-title text-success"><i class="fa fa-certificate"></i> {{ __('pages.member_portal_requests.certificate_ready_title') }}</h3>
                <p class="mb-3">{{ __('pages.member_portal_requests.certificate_ready_text', ['type' => $memberRequest->type->label()]) }}</p>
                <a href="{{ route('church.member.requests.certificate', $memberRequest) }}" class="btn btn-success">
                    <i class="fa fa-download"></i> {{ __('pages.member_portal_requests.download_certificate_pdf') }}
                </a>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="tile">
            <h3 class="tile-title">{{ __('pages.member_portal_requests.status_guide') }}</h3>
            <ul class="small mb-0 pl-3">
                <li>{{ __('pages.member_portal_requests.status_pending') }}</li>
                <li>{{ __('pages.member_portal_requests.status_in_review') }}</li>
                <li>{{ __('pages.member_portal_requests.status_approved') }}</li>
                <li>{{ __('pages.member_portal_requests.status_rejected') }}</li>
            </ul>
        </div>
    </div>
</div>

<a href="{{ route('church.member.requests.index') }}" class="btn btn-secondary">
    <i class="fa fa-arrow-left"></i> {{ __('pages.member_portal_requests.back_to_requests') }}
</a>
@endsection
