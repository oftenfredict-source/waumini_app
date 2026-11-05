<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="h5 mb-1">{{ __('owner.set.packages_heading') }}</h2>
        <p class="text-muted small mb-0">{{ __('owner.set.packages_help') }}</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#newPackageForm" aria-expanded="false" aria-controls="newPackageForm">
        <i class="bi bi-plus-lg me-1"></i> {{ __('owner.set.add_package') }}
    </button>
</div>

<div id="newPackageForm" class="collapse mb-4">
    <div class="card border" style="border-color:rgba(148,0,0,.25)!important;">
        <div class="card-body">
            <h3 class="h6 mb-3" style="color:#940000;">{{ __('owner.set.new_package') }}</h3>
            <form method="POST" action="{{ route('owner.settings.packages.store') }}">
                @csrf
                @include('owner.settings.partials.package-fields', ['package' => null, 'prefix' => 'new_'])
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('owner.set.create_package') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@forelse($packages as $package)
    <div class="card owner-panel-card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <strong>{{ $package->name }}</strong>
                @if($package->is_active)
                    <span class="badge rounded-pill" style="background:rgba(22,163,74,.12);color:#16a34a;">{{ __('common.active') }}</span>
                @else
                    <span class="badge rounded-pill text-bg-light border">{{ __('common.inactive') }}</span>
                @endif
                <span class="badge rounded-pill" style="background:rgba(8,145,178,.12);color:#0891b2;">
                    {{ __('owner.set.subscriptions_count', ['count' => $package->subscriptions_count]) }}
                </span>
            </div>
            <code class="small text-muted">{{ $package->slug }}</code>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('owner.settings.packages.update', $package) }}">
                @csrf
                @method('PUT')
                @include('owner.settings.partials.package-fields', ['package' => $package, 'prefix' => 'pkg'.$package->id.'_'])

                <h3 class="h6 mt-4 mb-3">{{ __('owner.set.features_included') }}</h3>
                <div class="row g-2">
                    @foreach($features as $feature)
                        @php
                            $enabled = $package->features->firstWhere('id', $feature->id)?->pivot->is_enabled ?? false;
                        @endphp
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="feature_{{ $package->id }}_{{ $feature->id }}"
                                    name="features[{{ $feature->id }}]" value="1"
                                    @checked(old("features.{$feature->id}", $enabled))>
                                <label class="form-check-label" for="feature_{{ $package->id }}_{{ $feature->id }}">{{ $feature->name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ __('owner.set.update_package') }}
                    </button>
                </div>
            </form>
            @if($package->subscriptions_count === 0)
                <form method="POST" action="{{ route('owner.settings.packages.destroy', $package) }}" class="mt-3"
                    data-swal-confirm="{{ __('owner.set.delete_package') }}"
                    data-swal-delete
                    data-swal-confirm-text="{{ __('common.yes_delete') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> {{ __('common.delete') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
@empty
    <p class="text-muted mb-0">{{ __('owner.set.no_packages') }}</p>
@endforelse

<hr class="my-4">
<h2 class="h5 mb-3">{{ __('owner.set.feature_overview') }}</h2>
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>{{ __('owner.set.feature') }}</th>
                @foreach($packages as $package)
                    <th class="text-center">{{ $package->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($features as $feature)
                <tr>
                    <td>
                        {{ $feature->name }}
                        <small class="text-muted">({{ $feature->module }})</small>
                    </td>
                    @foreach($packages as $package)
                        @php $on = $package->features->firstWhere('id', $feature->id)?->pivot->is_enabled ?? false; @endphp
                        <td class="text-center">
                            @if($on)
                                <i class="bi bi-check-lg text-success"></i>
                            @else
                                <i class="bi bi-x-lg text-muted"></i>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
