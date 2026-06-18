<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Subscription Packages</h4>
        <p class="text-muted mb-0">Set prices and limits for all churches on the platform.</p>
    </div>
    <button type="button" class="btn btn-primary" data-toggle="collapse" data-target="#newPackageForm">
        <i class="fa fa-plus"></i> Add Package
    </button>
</div>

<div id="newPackageForm" class="collapse mb-4">
    <div class="card card-body border-primary">
        <h5>New Package</h5>
        <form method="POST" action="{{ route('owner.settings.packages.store') }}">
            @csrf
            @include('owner.settings.partials.package-fields', ['package' => null, 'prefix' => 'new_'])
            <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Create Package</button>
        </form>
    </div>
</div>

@forelse($packages as $package)
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $package->name }}</strong>
                <span class="badge badge-{{ $package->is_active ? 'success' : 'secondary' }} ml-2">
                    {{ $package->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span class="badge badge-info ml-1">{{ $package->subscriptions_count }} subscriptions</span>
            </div>
            <code>{{ $package->slug }}</code>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('owner.settings.packages.update', $package) }}">
                @csrf
                @method('PUT')
                @include('owner.settings.partials.package-fields', ['package' => $package, 'prefix' => ''])

                <h6 class="mt-3 mb-2">Features included in this package</h6>
                <div class="row">
                    @foreach($features as $feature)
                        @php
                            $enabled = $package->features->firstWhere('id', $feature->id)?->pivot->is_enabled ?? false;
                        @endphp
                        <div class="col-md-4 col-lg-3">
                            <div class="animated-checkbox">
                                <label>
                                    <input type="checkbox" name="features[{{ $feature->id }}]" value="1"
                                        @if(old("features.{$feature->id}", $enabled)) checked @endif>
                                    <span class="label-text">{{ $feature->name }}</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Package</button>
                </div>
            </form>
            @if($package->subscriptions_count === 0)
                <form method="POST" action="{{ route('owner.settings.packages.destroy', $package) }}" class="mt-2"
                    data-swal-confirm="Delete this package?"
                    data-swal-delete
                    data-swal-confirm-text="Yes, delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i> Delete</button>
                </form>
            @endif
        </div>
    </div>
@empty
    <p class="text-muted">No packages yet. Create your first subscription package above.</p>
@endforelse

<hr class="my-4">
<h5>Feature Overview</h5>
<div class="table-responsive">
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Feature</th>
                @foreach($packages as $package)
                    <th class="text-center">{{ $package->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($features as $feature)
                <tr>
                    <td>{{ $feature->name }} <small class="text-muted">({{ $feature->module }})</small></td>
                    @foreach($packages as $package)
                        @php $on = $package->features->firstWhere('id', $feature->id)?->pivot->is_enabled ?? false; @endphp
                        <td class="text-center">
                            @if($on)<i class="fa fa-check text-success"></i>@else<i class="fa fa-times text-muted"></i>@endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
