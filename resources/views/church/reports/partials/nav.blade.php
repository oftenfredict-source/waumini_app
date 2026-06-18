<div class="mb-3 no-print">
    <a href="{{ route('church.reports.index', request()->only(['start_date', 'end_date'])) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> All Reports
    </a>
    <button type="button" class="btn btn-outline-primary btn-sm float-right" onclick="window.print()">
        <i class="fa fa-print"></i> Print
    </button>
</div>
