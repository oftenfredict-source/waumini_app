@php
    $assetModel = $asset ?? null;
    $showSequentialTagHint = old('registration_mode', 'lot') === 'individual'
        && (int) old('quantity', 1) > 1;
@endphp

<div class="row">
  @if($branchesEnabled ?? false)
    <div class="col-md-4">
      <div class="form-group">
        <label>Branch</label>
        <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror">
          <option value="">—</option>
          @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $assetModel?->branch_id ?? $defaultBranchId ?? '') === (string) $branch->id)>
              {{ $branch->displayLabel() }}
            </option>
          @endforeach
        </select>
        @error('branch_id')<small class="text-danger">{{ $message }}</small>@enderror
      </div>
    </div>
  @endif

  @if(! $assetModel)
    <div class="col-md-12">
      <div class="alert alert-info mb-3">
        <i class="fa fa-info-circle"></i>
        Asset tag will be assigned automatically
        @if(! empty($nextAssetTag))
          (next: <code>{{ $nextAssetTag }}</code>{{ $showSequentialTagHint ? ', then sequential tags for each item' : '' }}).
        @endif
      </div>
    </div>

    <div class="col-md-3">
      <div class="form-group">
        <label>Quantity *</label>
        <input type="number" name="quantity" id="asset_quantity" class="form-control @error('quantity') is-invalid @enderror"
          value="{{ old('quantity', 1) }}" min="1" max="500" required>
        <small class="text-muted">e.g. 50 for fifty identical chairs</small>
        @error('quantity')<small class="text-danger d-block">{{ $message }}</small>@enderror
      </div>
    </div>

    <div class="col-md-9">
      <div class="form-group">
        <label>How should these be recorded? *</label>
        <div class="mt-1">
          <div class="animated-radio">
            <label class="mr-4">
              <input type="radio" name="registration_mode" value="lot" @checked(old('registration_mode', 'lot') === 'lot')>
              <span class="label-text"><strong>One entry</strong> — single asset tag with quantity on file (best for identical items like chairs)</span>
            </label>
          </div>
          <div class="animated-radio mt-2">
            <label>
              <input type="radio" name="registration_mode" value="individual" @checked(old('registration_mode') === 'individual')>
              <span class="label-text"><strong>Separate tag each</strong> — creates one record per item, each with its own auto tag</span>
            </label>
          </div>
        </div>
        @error('registration_mode')<small class="text-danger d-block">{{ $message }}</small>@enderror
      </div>
    </div>
  @else
    <div class="col-md-4">
      <div class="form-group">
        <label>Asset Tag</label>
        <input type="text" class="form-control" value="{{ $assetModel->asset_tag }}" disabled>
      </div>
    </div>
    @if(! $assetModel->batch_id)
      <div class="col-md-4">
        <div class="form-group">
          <label>Quantity *</label>
          <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
            value="{{ old('quantity', $assetModel->quantity ?? 1) }}" min="1" max="500" required>
          @error('quantity')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
      </div>
    @else
      <div class="col-md-4">
        <div class="form-group">
          <label>Quantity</label>
          <input type="text" class="form-control" value="1 (part of bulk registration)" disabled>
        </div>
      </div>
    @endif
  @endif

  <div class="col-md-4">
    <div class="form-group">
      <label>Asset Name *</label>
      <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $assetModel?->name) }}" placeholder="e.g. Church PA System" required>
      @error('name')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label>Category *</label>
      <select name="category" class="form-control @error('category') is-invalid @enderror" required>
        <option value="">Select category</option>
        @foreach($categories as $category)
          <option value="{{ $category->value }}" @selected(old('category', $assetModel?->category?->value) === $category->value)>
            {{ $category->label() }}
          </option>
        @endforeach
      </select>
      @error('category')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label>Serial Number</label>
      <input type="text" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror"
        value="{{ old('serial_number', $assetModel?->serial_number) }}">
      @error('serial_number')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label>Location</label>
      <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
        value="{{ old('location', $assetModel?->location) }}" placeholder="e.g. Main Hall, Store room">
      @error('location')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label>Purchase Date</label>
      <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
        value="{{ old('purchase_date', $assetModel?->purchase_date?->toDateString()) }}" max="{{ now()->toDateString() }}">
      @error('purchase_date')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label>Purchase Value (TZS)</label>
      <input type="number" name="purchase_value" class="form-control @error('purchase_value') is-invalid @enderror"
        value="{{ old('purchase_value', $assetModel?->purchase_value) }}" min="0" step="0.01">
      @error('purchase_value')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label>Current Value (TZS)</label>
      <input type="number" name="current_value" class="form-control @error('current_value') is-invalid @enderror"
        value="{{ old('current_value', $assetModel?->current_value) }}" min="0" step="0.01">
      @error('current_value')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label>Condition *</label>
      <select name="condition" class="form-control @error('condition') is-invalid @enderror" required>
        @foreach($conditions as $condition)
          <option value="{{ $condition->value }}" @selected(old('condition', $assetModel?->condition?->value ?? 'good') === $condition->value)>
            {{ $condition->label() }}
          </option>
        @endforeach
      </select>
      @error('condition')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label>Status *</label>
      <select name="status" id="asset_status" class="form-control @error('status') is-invalid @enderror" required>
        @foreach($statuses as $status)
          <option value="{{ $status->value }}" @selected(old('status', $assetModel?->status?->value ?? 'active') === $status->value)>
            {{ $status->label() }}
          </option>
        @endforeach
      </select>
      @error('status')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4" id="disposedAtWrap" style="display:none;">
    <div class="form-group">
      <label>Disposed Date *</label>
      <input type="date" name="disposed_at" id="disposed_at" class="form-control @error('disposed_at') is-invalid @enderror"
        value="{{ old('disposed_at', $assetModel?->disposed_at?->toDateString()) }}" max="{{ now()->toDateString() }}">
      @error('disposed_at')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label>Custodian (Member)</label>
      <select name="custodian_member_id" class="form-control @error('custodian_member_id') is-invalid @enderror">
        <option value="">No custodian assigned</option>
        @foreach($members as $member)
          <option value="{{ $member->id }}" @selected((string) old('custodian_member_id', $assetModel?->custodian_member_id) === (string) $member->id)>
            {{ $member->full_name }} ({{ $member->member_number }})
          </option>
        @endforeach
      </select>
      @error('custodian_member_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label>Photo</label>
      <input type="file" name="photo" class="form-control-file @error('photo') is-invalid @enderror" accept="image/*">
      @if($assetModel && $assetModel->photoUrl())
        <small class="text-muted d-block mt-1">Current photo on file</small>
      @endif
      @error('photo')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <label>Description</label>
      <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2"
        placeholder="Brief description of the asset">{{ old('description', $assetModel?->description) }}</textarea>
      @error('description')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group mb-0">
      <label>Notes</label>
      <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2"
        placeholder="Additional notes">{{ old('notes', $assetModel?->notes) }}</textarea>
      @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
    var statusEl = document.getElementById('asset_status');
    var disposedWrap = document.getElementById('disposedAtWrap');
    var disposedAt = document.getElementById('disposed_at');

    function toggleDisposed() {
        if (!statusEl || !disposedWrap) return;
        var disposed = statusEl.value === 'disposed';
        disposedWrap.style.display = disposed ? 'block' : 'none';
        if (disposedAt) disposedAt.required = disposed;
    }

    if (statusEl) {
        statusEl.addEventListener('change', toggleDisposed);
        toggleDisposed();
    }
})();
</script>
@endpush
