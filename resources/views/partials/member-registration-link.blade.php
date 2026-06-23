@if(!empty($registrationUrl))
<div class="tile mb-3" id="memberRegistrationLinkCard">
    <div class="d-flex flex-wrap align-items-start justify-content-between">
        <div class="pr-3" style="flex: 1; min-width: 220px;">
            <h5 class="mb-2"><i class="fa fa-link text-primary"></i> Member self-registration link</h5>
            <p class="text-muted mb-2 small">
                Share this link with members so they can register online. Applications will appear under
                <strong>Registration Approvals</strong> for pastor or secretary review.
            </p>
        </div>
        <div style="flex: 1; min-width: 280px; max-width: 100%;">
            <label class="small text-muted mb-1">
                @if(config('waumini.use_subdomain_urls'))
                    Registration URL
                @else
                    Registration URL (works without subdomain DNS)
                @endif
            </label>
            <div class="input-group">
                <input type="text" class="form-control" id="memberRegistrationLinkInput" value="{{ $registrationUrl }}" readonly>
                <div class="input-group-append">
                    <button type="button" class="btn btn-primary" id="copyMemberRegistrationLinkBtn" title="Copy link">
                        <i class="fa fa-copy"></i> Copy
                    </button>
                    <a href="{{ $registrationUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary" title="Open link">
                        <i class="fa fa-external-link"></i>
                    </a>
                </div>
            </div>
            <small id="memberRegistrationLinkStatus" class="form-text text-success" style="display:none;">Link copied to clipboard.</small>

            @if(!config('waumini.use_subdomain_urls') && !empty($registrationSubdomainUrl))
                <label class="small text-muted mb-1 mt-3">Subdomain URL (after wildcard DNS is configured)</label>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ $registrationSubdomainUrl }}" readonly>
                    <div class="input-group-append">
                        <a href="{{ $registrationSubdomainUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary" title="Open subdomain link">
                            <i class="fa fa-external-link"></i>
                        </a>
                    </div>
                </div>
                <small class="form-text text-muted">
                    Set <code>TENANT_USE_SUBDOMAIN_URLS=true</code> in <code>.env</code> after adding wildcard DNS for <code>*.{{ config('waumini.base_domain') }}</code>.
                </small>
            @endif
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    const btn = document.getElementById('copyMemberRegistrationLinkBtn');
    const input = document.getElementById('memberRegistrationLinkInput');
    const status = document.getElementById('memberRegistrationLinkStatus');
    if (!btn || !input) return;

    btn.addEventListener('click', function () {
        const text = input.value;
        const done = function () {
            if (status) {
                status.style.display = 'block';
                setTimeout(function () { status.style.display = 'none'; }, 2500);
            }
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done);
            return;
        }

        input.select();
        input.setSelectionRange(0, text.length);
        try {
            document.execCommand('copy');
            done();
        } catch (e) {
            alert('Copy this link manually:\n' + text);
        }
    });
})();
</script>
@endpush
@endonce
@endif
