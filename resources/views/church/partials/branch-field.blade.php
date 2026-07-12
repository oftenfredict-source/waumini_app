@if(($branchesEnabled ?? false) && ($canSelectBranch ?? false) && ($branches ?? collect())->isNotEmpty())
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ __('pages.branches.item') }} *</label>
            <select name="branch_id" class="form-control @error('branch_id') is-invalid @enderror" required>
                <option value="">{{ __('pages.shared.select') }}...</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((string) old('branch_id', $defaultBranchId ?? '') === (string) $branch->id)>
                        {{ $branch->displayLabel() }}
                    </option>
                @endforeach
            </select>
            @error('branch_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
@elseif(($branchesEnabled ?? false) && ($defaultBranchId ?? null))
    <input type="hidden" name="branch_id" value="{{ $defaultBranchId }}">
@endif
