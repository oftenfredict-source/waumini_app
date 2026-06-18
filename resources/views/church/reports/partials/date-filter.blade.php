<div class="tile mb-3 no-print">
    <form method="GET" class="form-row align-items-end">
        @foreach(request()->except(['start_date', 'end_date', 'page']) as $key => $value)
            @if(is_string($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <div class="form-group col-md-4">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="form-group col-md-4">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $end->format('Y-m-d') }}">
        </div>
        <div class="form-group col-md-4">
            <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Apply</button>
        </div>
    </form>
</div>
