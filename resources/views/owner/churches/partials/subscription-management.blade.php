@can('manageSubscription', $church)
<div class="tile">
    <h3 class="tile-title"><i class="fa fa-credit-card"></i> {{ __('owner.church.subscription_management') }}</h3>
    <p class="text-muted">{{ __('owner.church.subscription_management_help') }}</p>

    @if($packages->isEmpty())
        <div class="alert alert-warning mb-0">
            {{ __('owner.set.no_packages') }}
            <a href="{{ route('owner.settings.index', ['tab' => 'packages']) }}">{{ __('owner.set.add_package') }}</a>
        </div>
    @else
        @if($church->activeSubscription)
            <div class="alert alert-light border mb-3">
                <strong>{{ __('owner.church.current_plan') }}:</strong>
                {{ $church->activeSubscription->package?->name ?? '—' }}
                <span class="badge badge-{{ $church->activeSubscription->status->value === 'active' ? 'success' : 'info' }} ml-1">
                    {{ ucfirst(str_replace('_', ' ', $church->activeSubscription->status->value)) }}
                </span>
                @if($church->activeSubscription->trial_ends_at)
                    <br><small class="text-muted">{{ __('owner.church.trial_ends_on', ['date' => $church->activeSubscription->trial_ends_at->format('M d, Y')]) }}</small>
                @elseif($church->activeSubscription->ends_at)
                    <br><small class="text-muted">{{ __('owner.church.subscription_ends_on', ['date' => $church->activeSubscription->ends_at->format('M d, Y')]) }}</small>
                @endif
            </div>
        @else
            <div class="alert alert-warning py-2 mb-3">
                <i class="fa fa-info-circle"></i> {{ __('owner.church.no_subscription_assigned') }}
            </div>
        @endif

        <form method="POST" action="{{ route('owner.churches.subscription.store', $church) }}" id="ownerSubscriptionForm">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __('owner.package') }} <span class="text-danger">*</span></label>
                        <select name="package_id" id="subscription_package_id" class="form-control" required>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}"
                                    data-installation="{{ $package->installation_price }}"
                                    data-yearly="{{ $package->yearly_price }}"
                                    @selected(old('package_id', $church->activeSubscription?->package_id) == $package->id)>
                                    {{ $package->name }}
                                    ({{ $platformCurrency }} {{ number_format($package->installation_price, 0) }} + {{ number_format($package->yearly_price, 0) }}/yr)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __('owner.church.subscription_action') }} <span class="text-danger">*</span></label>
                        <select name="action" id="subscription_action" class="form-control" required>
                            <option value="activate" @selected(old('action', 'activate') === 'activate')>{{ __('owner.church.action_activate_paid') }}</option>
                            <option value="assign_trial" @selected(old('action') === 'assign_trial')>{{ __('owner.church.action_assign_trial') }}</option>
                        </select>
                        <small class="text-muted" id="subscription_action_help">{{ __('owner.church.action_activate_help') }}</small>
                    </div>
                </div>
            </div>

            <div id="subscription_payment_fields">
                <h5 class="mt-2 mb-3">{{ __('owner.church.record_payment') }}</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="animated-checkbox mb-2">
                            <label>
                                <input type="checkbox" name="record_installation" value="1" id="record_installation"
                                       @checked(old('record_installation', true))>
                                <span class="label-text">{{ __('owner.church.record_installation') }}</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>{{ __('owner.church.installation_amount') }}</label>
                            <input type="number" name="installation_amount" id="installation_amount" class="form-control"
                                   min="0" step="0.01" value="{{ old('installation_amount') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="animated-checkbox mb-2">
                            <label>
                                <input type="checkbox" name="record_yearly" value="1" id="record_yearly"
                                       @checked(old('record_yearly', true))>
                                <span class="label-text">{{ __('owner.church.record_yearly') }}</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>{{ __('owner.church.yearly_amount') }}</label>
                            <input type="number" name="yearly_amount" id="yearly_amount" class="form-control"
                                   min="0" step="0.01" value="{{ old('yearly_amount') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{ __('owner.pay.method') }}</label>
                            <select name="method" class="form-control">
                                <option value="cash" @selected(old('method', 'cash') === 'cash')>{{ __('owner.church.pay_cash') }}</option>
                                <option value="bank_transfer" @selected(old('method') === 'bank_transfer')>{{ __('owner.church.pay_bank') }}</option>
                                <option value="mobile_money" @selected(old('method') === 'mobile_money')>{{ __('owner.church.pay_mobile') }}</option>
                                <option value="cheque" @selected(old('method') === 'cheque')>{{ __('owner.church.pay_cheque') }}</option>
                                <option value="other" @selected(old('method') === 'other')>{{ __('owner.church.pay_other') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{ __('common.reference') }}</label>
                            <input type="text" name="provider_reference" class="form-control"
                                   value="{{ old('provider_reference') }}" placeholder="{{ __('owner.church.payment_ref_placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>{{ __('owner.church.payment_notes') }}</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success" id="subscription_submit_btn"
                    data-swal-confirm="{{ __('owner.church.subscription_submit_confirm') }}">
                <i class="fa fa-check"></i> {{ __('owner.church.subscription_submit') }}
            </button>
        </form>

        @if($recentPayments->isNotEmpty())
            <hr class="my-4">
            <h5>{{ __('owner.church.recent_payments') }}</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('common.date') }}</th>
                            <th>{{ __('common.type') }}</th>
                            <th>{{ __('common.amount') }}</th>
                            <th>{{ __('owner.pay.method') }}</th>
                            <th>{{ __('common.reference') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPayments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', (string) data_get($payment->metadata, 'type', 'payment'))) }}</td>
                                <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                <td>{{ $payment->provider_reference ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const packageSelect = document.getElementById('subscription_package_id');
    const actionSelect = document.getElementById('subscription_action');
    const paymentFields = document.getElementById('subscription_payment_fields');
    const installationAmount = document.getElementById('installation_amount');
    const yearlyAmount = document.getElementById('yearly_amount');
    const actionHelp = document.getElementById('subscription_action_help');
    const submitBtn = document.getElementById('subscription_submit_btn');

    if (!packageSelect) {
        return;
    }

    const helpActivate = @json(__('owner.church.action_activate_help'));
    const helpTrial = @json(__('owner.church.action_assign_trial_help'));
    const labelActivate = @json(__('owner.church.subscription_submit'));
    const labelTrial = @json(__('owner.church.subscription_submit_trial'));

    function syncPackageAmounts() {
        const option = packageSelect.options[packageSelect.selectedIndex];
        if (!option) return;

        if (installationAmount && !installationAmount.dataset.touched) {
            installationAmount.value = option.dataset.installation || '';
        }
        if (yearlyAmount && !yearlyAmount.dataset.touched) {
            yearlyAmount.value = option.dataset.yearly || '';
        }
    }

    function syncActionUi() {
        if (!actionSelect || !paymentFields) return;
        const isActivate = actionSelect.value === 'activate';
        paymentFields.style.display = isActivate ? '' : 'none';
        if (actionHelp) {
            actionHelp.textContent = isActivate ? helpActivate : helpTrial;
        }
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fa fa-check"></i> ' + (isActivate ? labelActivate : labelTrial);
        }
    }

    installationAmount?.addEventListener('input', () => installationAmount.dataset.touched = '1');
    yearlyAmount?.addEventListener('input', () => yearlyAmount.dataset.touched = '1');
    packageSelect.addEventListener('change', () => {
        if (installationAmount) delete installationAmount.dataset.touched;
        if (yearlyAmount) delete yearlyAmount.dataset.touched;
        syncPackageAmounts();
    });
    actionSelect?.addEventListener('change', syncActionUi);

    syncPackageAmounts();
    syncActionUi();
});
</script>
@endpush
@endcan
