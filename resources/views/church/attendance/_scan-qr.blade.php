@php
    $source = $source ?? null;
    $hideQr = $hideQr ?? false;
    if ($source && method_exists($source, 'isSundaySchool') && $source->isSundaySchool()) {
        $hideQr = true;
    }
    $cancelled = false;
    if ($source && isset($source->status)) {
        $cancelled = $source->status->value === 'cancelled';
    }
@endphp
@if(! $hideQr && ! $cancelled)
    @php
        $checkInUrl = \App\Services\Church\AttendanceService::scanUrl($sourceType, (int) $sourceId);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&ecc=M&data='.urlencode($checkInUrl);
    @endphp
    <div class="tile">
        <h3 class="tile-title">{{ __('pages.attendance.scan_qr_title') }}</h3>
        <p class="text-muted mb-3">{{ __('pages.attendance.scan_qr_help') }}</p>
        <div class="text-center">
            <img src="{{ $qrUrl }}" alt="Attendance QR" width="260" height="260"
                style="background:#fff;padding:10px;border-radius:12px;border:1px solid #eee;">
            <p class="small text-muted mt-2 mb-3">{{ __('pages.attendance.scan_qr_camera_hint') }}</p>
            <a href="{{ route('church.attendance.qr', ['source_type' => $sourceType, 'source_id' => $sourceId]) }}"
               class="btn btn-outline-primary btn-sm" target="_blank">
                <i class="fa fa-print"></i> {{ __('pages.attendance.print_qr') }}
            </a>
        </div>
    </div>
@endif
