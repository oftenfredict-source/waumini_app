<div class="signature">
    <table class="signature-table" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="55%" valign="bottom" style="padding-right: 20px;">
                @if(!empty($pastorSignatureBase64))
                    <img src="{{ $pastorSignatureBase64 }}" alt="" class="signature-image">
                @endif
                    <div class="signature-line">
                        <strong>{{ $signatoryName }}</strong><br>
                        {{ $signatoryTitle }}<br>
                        {{ $displayName ?? $church->name }}
                    </div>
            </td>
            <td width="45%" valign="bottom" align="center">
                @if(!empty($churchSealBase64))
                    <img src="{{ $churchSealBase64 }}" alt="" class="seal-image">
                    <div class="seal-caption">{{ __('certificates.church_seal') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>
