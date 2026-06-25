@push('styles')
<style>
    .report-hero {
        background: linear-gradient(135deg, #940000 0%, #600000 100%);
        border-radius: 8px;
        color: #fff;
        padding: 1.75rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 8px 24px rgba(148, 0, 0, 0.18);
    }
    .report-hero h2 { color: #fff; margin-bottom: 0.35rem; font-weight: 600; }
    .report-hero .lead { color: rgba(255,255,255,0.85); margin-bottom: 0; }
    .report-stat-card {
        border: none; border-radius: 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); height: 100%;
    }
    .report-stat-card .card-body { padding: 1.25rem 1.35rem; }
    .report-stat-value { font-size: 1.35rem; font-weight: 700; }
    .report-stat-label {
        color: #6c757d; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.04em;
    }
    .report-card-link {
        display: block; height: 100%; padding: 1.25rem; border: 1px solid #eee;
        border-radius: 10px; color: #333; transition: all 0.2s ease; text-decoration: none;
    }
    .report-card-link:hover {
        text-decoration: none; border-color: #940000; color: #940000;
        box-shadow: 0 6px 18px rgba(148,0,0,0.08); transform: translateY(-2px);
    }
    .report-card-link i { font-size: 1.75rem; color: #940000; margin-bottom: 0.75rem; }
    .report-chart-wrap { position: relative; min-height: 260px; max-width: 100%; }
    @media (max-width: 767.98px) {
        .report-hero {
            padding: 1.25rem 1rem;
            border-radius: 6px;
        }
        .report-hero h2 { font-size: 1.25rem; }
        .report-stat-value { font-size: 1.15rem; }
        .report-chart-wrap { min-height: 220px; }
    }
    @media print {
        .app-sidebar, .app-header, .app-sidebar__toggle, .no-print { display: none !important; }
        .app-content { margin-left: 0 !important; padding: 0 !important; }
    }
</style>
@endpush
