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

        @if($memberRequest->type->generatesCertificate())
            @can('previewCertificate', $memberRequest)
                <div class="tile">
                    <h3 class="tile-title"><i class="fa fa-certificate"></i> {{ __('pages.member_requests.certificate_review_title') }}</h3>

                    @if($memberRequest->isCertificateReleased())
                        <div class="alert alert-success py-2 small mb-3">
                            {{ __('pages.member_requests.certificate_released_notice', [
                                'datetime' => $memberRequest->certificate_released_at?->format('M d, Y g:i A') ?? '—',
                            ]) }}
                        </div>
                    @elseif($memberRequest->hasGeneratedCertificate())
                        <div class="alert alert-warning py-2 small mb-3">
                            {{ __('pages.member_requests.certificate_awaiting_release') }}
                        </div>
                    @else
                        <div class="alert alert-info py-2 small mb-3">
                            {{ __('pages.member_requests.certificate_preview_hint') }}
                        </div>
                    @endif

                    @if($memberRequest->certificate_generated_at)
                        <p class="mb-2 text-muted small">
                            {{ __('pages.member_requests.certificate_generated', [
                                'datetime' => $memberRequest->certificate_generated_at->format('M d, Y g:i A'),
                            ]) }}
                        </p>
                    @endif

                    <div class="mb-3 border rounded overflow-hidden" style="background:#f5f5f5;">
                        <iframe
                            title="{{ __('pages.member_requests.certificate_review_title') }}"
                            src="{{ route('church.member-requests.certificate.preview', $memberRequest) }}"
                            style="width:100%; height:640px; border:0; display:block;"
                        ></iframe>
                    </div>

                    <div class="d-flex flex-wrap" style="gap:0.5rem;">
                        <a href="{{ route('church.member-requests.certificate.preview', $memberRequest) }}" target="_blank" class="btn btn-outline-secondary">
                            <i class="fa fa-external-link"></i> {{ __('pages.member_requests.open_preview') }}
                        </a>
                        @if($memberRequest->hasGeneratedCertificate())
                            <a href="{{ route('church.member-requests.certificate', $memberRequest) }}" class="btn btn-outline-primary">
                                <i class="fa fa-download"></i> {{ __('pages.member_requests.download_certificate_pdf') }}
                            </a>
                        @endif
                        @can('releaseCertificate', $memberRequest)
                            @if($memberRequest->hasGeneratedCertificate() && ! $memberRequest->isCertificateReleased())
                                <form method="POST" action="{{ route('church.member-requests.certificate.release', $memberRequest) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                            onclick="return confirm(@json(__('pages.member_requests.release_confirm')))">
                                        <i class="fa fa-check"></i> {{ __('pages.member_requests.release_to_member') }}
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
            @endcan
        @endif
    </div>

    @can('respond', $memberRequest)
        <div class="col-lg-5">
            <div class="tile">
                <h3 class="tile-title">{{ __('pages.member_requests.update_request') }}</h3>
                @if($memberRequest->type->generatesCertificate())
                    @php
                        $hasSeal = $memberRequest->church?->hasOfficialSeal();
                        $hasSignature = $memberRequest->church?->hasPastorSignature();
                        $hasTemplate = $memberRequest->church?->hasCertificateTemplate($memberRequest->type->value);
                    @endphp
                    <div class="alert alert-info py-2 small">
                        {!! __('pages.member_requests.certificate_approve_alert', ['type' => $memberRequest->type->label()]) !!}
                    </div>
                    <ul class="list-unstyled small mb-3">
                        <li class="mb-1">
                            @if($hasTemplate)
                                <i class="fa fa-check-circle text-success"></i> {{ __('pages.member_requests.template_ready') }}
                            @else
                                <i class="fa fa-info-circle text-muted"></i> {{ __('pages.member_requests.template_missing') }}
                            @endif
                        </li>
                        <li class="mb-1">
                            @if($hasSeal)
                                <i class="fa fa-check-circle text-success"></i> {{ __('pages.member_requests.seal_ready') }}
                            @else
                                <i class="fa fa-exclamation-circle text-warning"></i> {{ __('pages.member_requests.seal_missing') }}
                            @endif
                        </li>
                        <li class="mb-1">
                            @if($hasSignature)
                                <i class="fa fa-check-circle text-success"></i> {{ __('pages.member_requests.signature_ready') }}
                            @else
                                <i class="fa fa-exclamation-circle text-warning"></i> {{ __('pages.member_requests.signature_missing') }}
                            @endif
                        </li>
                    </ul>
                    @if(! $hasSeal || ! $hasSignature)
                        <p class="small text-muted mb-3">
                            {{ __('pages.member_requests.official_tools_missing') }}
                            <a href="{{ route('church.system.settings.index', ['tab' => 'official']) }}">{{ __('pages.member_requests.manage_official_tools') }}</a>
                        </p>
                    @endif
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
