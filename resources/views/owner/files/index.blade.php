@extends('layouts.owner')

@section('title', __('owner.files.title'))

@section('content')
@php
    $storageBytes = (int) ($stats['storage_bytes'] ?? 0);
    if ($storageBytes < 1048576) {
        $storageLabel = number_format($storageBytes / 1024, 1).' KB';
    } else {
        $storageLabel = number_format($storageBytes / 1048576, 2).' MB';
    }
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('owner.files.title') }}</h1>
        <p class="text-muted mb-0">{{ __('owner.files.subtitle') }}</p>
    </div>
    @can('create', \App\Models\OwnerFile::class)
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadOwnerFileModal">
            <i class="bi bi-cloud-upload me-1"></i> {{ __('owner.files.upload') }}
        </button>
    @endcan
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-folder2"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.files.total') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(8,145,178,.12);color:#0891b2;">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.files.categories.document') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['documents']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(217,119,6,.12);color:#d97706;">
                        <i class="bi bi-image"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.files.categories.image') }}</p>
                        <div class="h3 mb-0">{{ number_format($stats['images']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon" style="background:rgba(22,163,74,.12);color:#16a34a;">
                        <i class="bi bi-hdd"></i>
                    </div>
                    <div class="ms-3">
                        <p class="h6 mb-0 text-muted">{{ __('owner.files.storage_used') }}</p>
                        <div class="h3 mb-0">{{ $storageLabel }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card owner-panel-card">
    <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <span>{{ __('owner.files.menu') }}</span>
            <span class="badge text-bg-light border">{{ $files->total() }}</span>
        </div>
        <form method="GET" class="d-flex align-items-center flex-nowrap gap-2">
            <input type="text" name="search" class="form-control form-control-sm" style="min-width:10rem;max-width:16rem;"
                   placeholder="{{ __('owner.files.search_placeholder') }}" value="{{ $filters['search'] ?? '' }}">
            <select name="category" class="form-select form-select-sm" style="width:auto;min-width:8rem;">
                <option value="">{{ __('owner.files.all_categories') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>
                        {{ __('owner.files.categories.'.$category) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                <i class="bi bi-funnel me-1"></i> {{ __('common.filter') }}
            </button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('owner.files.file') }}</th>
                        <th>{{ __('owner.files.category') }}</th>
                        <th>{{ __('owner.files.size') }}</th>
                        <th>{{ __('owner.files.uploaded_by') }}</th>
                        <th>{{ __('common.created') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($files as $file)
                        @php
                            $categoryStyles = [
                                'document' => 'background:rgba(8,145,178,.12);color:#0891b2;',
                                'image' => 'background:rgba(217,119,6,.12);color:#d97706;',
                                'archive' => 'background:rgba(124,58,237,.12);color:#7c3aed;',
                                'other' => 'background:rgba(82,82,91,.1);color:#52525b;',
                            ];
                            $style = $categoryStyles[$file->category] ?? $categoryStyles['other'];
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold" style="color:#940000;">{{ $file->title }}</div>
                                <div class="small text-muted">{{ $file->original_name }}</div>
                                @if($file->description)
                                    <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($file->description, 80) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill" style="{{ $style }}">{{ $file->categoryLabel() }}</span>
                            </td>
                            <td class="text-nowrap">{{ $file->formattedSize() }}</td>
                            <td>{{ $file->uploader?->name ?? '—' }}</td>
                            <td class="text-nowrap">{{ $file->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-end text-nowrap">
                                @can('download', $file)
                                    <a href="{{ route('owner.files.download', $file) }}" class="btn btn-sm btn-outline-primary" title="{{ __('owner.files.download') }}">
                                        <i class="bi bi-download"></i>
                                    </a>
                                @endcan
                                @can('delete', $file)
                                    <form method="POST" action="{{ route('owner.files.destroy', $file) }}" class="d-inline"
                                          data-swal-confirm="{{ __('owner.files.delete_confirm') }}"
                                          data-swal-delete
                                          data-swal-confirm-text="{{ __('common.yes_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('common.delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-folder2-open d-block mb-2" style="font-size:1.75rem;"></i>
                                {{ __('owner.files.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($files->hasPages())
        <div class="card-footer bg-white">
            {{ $files->links() }}
        </div>
    @endif
</div>

@can('create', \App\Models\OwnerFile::class)
<div class="modal fade" id="uploadOwnerFileModal" tabindex="-1" aria-hidden="true"
     @if($errors->any()) data-show-on-load="1" @endif>
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('owner.files.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('owner.files.upload') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">{{ __('owner.files.upload_help') }}</p>
                    <div class="d-grid gap-3">
                        <div>
                            <label class="form-label" for="owner_file_title">{{ __('owner.files.file_title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="owner_file_title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" maxlength="180" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="owner_file_category">{{ __('owner.files.category') }}</label>
                            <select name="category" id="owner_file_category" class="form-select @error('category') is-invalid @enderror">
                                <option value="">{{ __('owner.files.auto_detect') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>
                                        {{ __('owner.files.categories.'.$category) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="owner_file_input">{{ __('owner.files.choose_file') }} <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="owner_file_input" class="form-control @error('file') is-invalid @enderror" required>
                            <div class="form-text">{{ __('owner.files.allowed_types') }}</div>
                            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="form-label" for="owner_file_description">{{ __('owner.files.description') }}</label>
                            <textarea name="description" id="owner_file_description" rows="3" maxlength="1000"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-1"></i> {{ __('owner.files.upload') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('uploadOwnerFileModal');
    if (modal && modal.getAttribute('data-show-on-load') === '1' && window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }
});
</script>
@endpush
@endcan
@endsection
