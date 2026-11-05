<form method="POST" action="{{ route('church.system.settings.update', 'official') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="alert alert-info">
        {{ __('pages.system_settings.official_intro') }}
    </div>

    <h5 class="mb-3">{{ __('pages.system_settings.church_seal') }}</h5>
    <p class="text-muted mb-3">{{ __('pages.system_settings.church_seal_help') }}</p>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                @if($settings['church_seal_url'])
                    <img src="{{ $settings['church_seal_url'] }}" alt="{{ __('pages.system_settings.church_seal') }}"
                         style="max-height: 110px; max-width: 110px; object-fit: contain; border: 1px solid #eee; padding: 8px; border-radius: 6px; background: #fff;">
                @else
                    <div class="text-muted border rounded p-3" style="min-width: 140px; text-align: center;">
                        <i class="fa fa-stamp fa-2x mb-2 d-block"></i>
                        {{ __('pages.system_settings.no_seal') }}
                    </div>
                @endif
                <div>
                    <div class="form-group mb-2">
                        <label>{{ __('pages.system_settings.upload_seal') }}</label>
                        <input type="file" name="seal" class="form-control-file @error('seal') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        @error('seal')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted d-block mt-1">{{ __('pages.system_settings.image_upload_hint') }}</small>
                    </div>
                    @if($settings['church_seal_url'])
                        <div class="form-check">
                            <input type="checkbox" name="remove_seal" value="1" class="form-check-input" id="remove_seal" @checked(old('remove_seal'))>
                            <label class="form-check-label" for="remove_seal">{{ __('pages.system_settings.remove_seal') }}</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h5 class="mb-3">{{ __('pages.system_settings.pastor_signature') }}</h5>
    <p class="text-muted mb-3">{{ __('pages.system_settings.pastor_signature_help') }}</p>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                @if($settings['pastor_signature_url'])
                    <img src="{{ $settings['pastor_signature_url'] }}" alt="{{ __('pages.system_settings.pastor_signature') }}"
                         style="max-height: 80px; max-width: 220px; object-fit: contain; border: 1px solid #eee; padding: 8px; border-radius: 6px; background: #fff;">
                @else
                    <div class="text-muted border rounded p-3" style="min-width: 180px; text-align: center;">
                        <i class="fa fa-pencil fa-2x mb-2 d-block"></i>
                        {{ __('pages.system_settings.no_signature') }}
                    </div>
                @endif
                <div>
                    <div class="form-group mb-2">
                        <label>{{ __('pages.system_settings.upload_signature') }}</label>
                        <input type="file" name="pastor_signature" class="form-control-file @error('pastor_signature') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        @error('pastor_signature')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted d-block mt-1">{{ __('pages.system_settings.image_upload_hint') }}</small>
                    </div>
                    @if($settings['pastor_signature_url'])
                        <div class="form-check">
                            <input type="checkbox" name="remove_pastor_signature" value="1" class="form-check-input" id="remove_pastor_signature" @checked(old('remove_pastor_signature'))>
                            <label class="form-check-label" for="remove_pastor_signature">{{ __('pages.system_settings.remove_signature') }}</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <hr>

    <h5 class="mb-2">{{ __('pages.system_settings.certificate_templates') }}</h5>
    <p class="text-muted mb-4">{{ __('pages.system_settings.certificate_templates_help') }}</p>

    @php
        $certificateTemplates = [
            'baptism_certificate' => __('enums.member_request_type.baptism_certificate'),
            'travelling_certificate' => __('enums.member_request_type.travelling_certificate'),
            'recommendation_letter' => __('enums.member_request_type.recommendation_letter'),
        ];
        $templateUrls = $settings['certificate_template_urls'] ?? [];
    @endphp

    @foreach($certificateTemplates as $templateKey => $templateLabel)
        @php
            $templateUrl = $templateUrls[$templateKey] ?? null;
            $fileName = 'template_'.$templateKey;
            $removeName = 'remove_template_'.$templateKey;
        @endphp
        <div class="mb-4 pb-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
            <h6 class="mb-3">{{ $templateLabel }}</h6>
            <div class="d-flex flex-wrap align-items-start gap-3">
                @if($templateUrl)
                    <img src="{{ $templateUrl }}" alt="{{ $templateLabel }}"
                         style="max-height: 160px; max-width: 120px; object-fit: contain; border: 1px solid #eee; padding: 6px; border-radius: 6px; background: #fff;">
                @else
                    <div class="text-muted border rounded p-3" style="min-width: 120px; text-align: center;">
                        <i class="fa fa-file-image-o fa-2x mb-2 d-block"></i>
                        {{ __('pages.system_settings.no_template') }}
                    </div>
                @endif
                <div class="flex-grow-1">
                    <div class="form-group mb-2">
                        <label>{{ __('pages.system_settings.upload_template') }}</label>
                        <input type="file" name="{{ $fileName }}" class="form-control-file @error($fileName) is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                        @error($fileName)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted d-block mt-1">{{ __('pages.system_settings.template_upload_hint') }}</small>
                    </div>
                    @if($templateUrl)
                        <div class="form-check">
                            <input type="checkbox" name="{{ $removeName }}" value="1" class="form-check-input" id="{{ $removeName }}" @checked(old($removeName))>
                            <label class="form-check-label" for="{{ $removeName }}">{{ __('pages.system_settings.remove_template') }}</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('pages.system_settings.save_official') }}</button>
</form>
