@if(session(\App\Services\Owner\ChurchImpersonationService::SESSION_OWNER_ID))
<div class="owner-impersonation-banner">
    <div class="owner-impersonation-banner__content">
        <i class="fa fa-user-secret"></i>
        <span>{{ __('owner.church.impersonation_banner', ['church' => auth()->user()->church?->name ?? '']) }}</span>
    </div>
    <form action="{{ route('church.impersonation.leave') }}" method="POST" class="mb-0">
        @csrf
        <button type="submit" class="btn btn-sm btn-light">
            <i class="fa fa-arrow-left"></i> {{ __('owner.church.return_to_owner') }}
        </button>
    </form>
</div>
@push('styles')
<style>
    .owner-impersonation-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        color: #fff;
        padding: 0.65rem 1.25rem;
        border-bottom: 3px solid #940000;
        position: sticky;
        top: 0;
        z-index: 1031;
    }
    .owner-impersonation-banner__content {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 500;
    }
</style>
@endpush
@endif
