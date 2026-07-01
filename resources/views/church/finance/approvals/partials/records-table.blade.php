@if($records->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm">
            <thead>
                <tr>
                    @if($canApprove)
                        <th width="40">
                            <input type="checkbox" class="select-all-pending" data-type="{{ $type }}">
                        </th>
                    @endif
                    <th>{{ __('common.member') }}</th>
                    <th>{{ __('common.amount') }}</th>
                    <th>{{ __('common.date') }}</th>
                    <th>{{ __('finance.payment') }}</th>
                    <th>{{ __('common.reference') }}</th>
                    <th>{{ __('pages.shared.recorded_by') }}</th>
                    <th width="140">{{ __('common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                    <tr data-record-type="{{ $type }}" data-record-id="{{ $record->id }}">
                        @if($canApprove)
                            <td>
                                <input type="checkbox" class="pending-record-checkbox" data-type="{{ $type }}" value="{{ $record->id }}">
                            </td>
                        @endif
                        <td>
                            @if($type === 'budget')
                                <strong>{{ $record->budget_name }}</strong>
                            @elseif($type === 'expense')
                                <strong>{{ $record->budget?->budget_name ?? '—' }}</strong>
                            @else
                                @php
                                    $member = $type === 'pledge_payment' ? $record->pledge?->member : $record->member;
                                @endphp
                                @if($type === 'offering' && ! $member)
                                    <strong>{{ __('finance.general_offering') }} — {{ $record->churchService?->displayTitle() ?? __('finance.offerings') }}</strong>
                                    @if($record->churchService)
                                        <br><small class="text-muted">{{ $record->churchService->service_date?->format('M d, Y') }}</small>
                                    @endif
                                @else
                                    <strong>{{ $member?->full_name ?? __('finance.general_anonymous') }}</strong>
                                    @if($member?->envelope_number)
                                        <br><small class="text-muted">{{ $member->envelope_number }}</small>
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td>
                            <strong class="text-success">
                                TZS {{ number_format($type === 'budget' ? (float) $record->total_budget : (float) $record->amount, 2) }}
                            </strong>
                        </td>
                        <td>{{ ($dateField ? $record->{$dateField} : $record->created_at)?->format('M d, Y') }}</td>
                        <td>{{ $record->payment_method?->label() ?? '—' }}</td>
                        <td>{{ $record->reference_number ?? '—' }}</td>
                        <td>{{ $record->recorder?->name ?? '—' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if($canApprove)
                                    <button type="button" class="btn btn-success btn-approve-record"
                                        data-type="{{ $type }}" data-id="{{ $record->id }}" title="{{ __('finance.approve') }}">
                                        <i class="fa fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-reject-record"
                                        data-type="{{ $type }}" data-id="{{ $record->id }}" title="{{ __('finance.reject') }}">
                                        <i class="fa fa-times"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-info btn-view-record"
                                    data-type="{{ $type }}" data-id="{{ $record->id }}" title="{{ __('common.view') }}">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center text-muted py-5">
        <i class="fa fa-check-circle fa-3x mb-3 text-success"></i>
        <h5>{{ __('finance.no_pending', ['label' => $label]) }}</h5>
        <p class="mb-0">{{ __('finance.all_processed', ['label' => strtolower($label)]) }}</p>
    </div>
@endif
