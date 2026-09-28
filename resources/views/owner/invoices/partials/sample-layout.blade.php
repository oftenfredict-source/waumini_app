@php
    use App\Support\EmcaInvoiceLogo;

    $issuedDate = $invoice->issued_at->format('d-F-Y');
    $currencyLabel = $invoice->currency === 'TZS' ? 'Tanzania Shillings' : $invoice->currency;
    $logoSrc = EmcaInvoiceLogo::base64();
    $bankName = trim((string) ($company['bank_name'] ?? ''));
    $bankParts = preg_split('/\s+/', $bankName, 2);
@endphp
<div class="emca-invoice">
    <table class="emca-main">
        <tr>
            <td class="emca-col-left">
                <div class="emca-header-left">
                    <div class="emca-invoice-title">INVOICE</div>
                    <table class="emca-title-line" cellpadding="0" cellspacing="0" border="0" role="presentation">
                        <tr>
                            <td bgcolor="#940000" height="6" width="210" style="background-color:#940000;font-size:1px;line-height:6px;mso-line-height-rule:exactly;">&nbsp;</td>
                        </tr>
                    </table>
                </div>

                <div class="emca-company">
                    <strong>{{ $company['company_name'] }}</strong><br>
                    {!! nl2br(e($company['company_address'])) !!}<br>
                    Phone: {{ $company['company_phone'] }}<br>
                    TIN: {{ $company['company_tin'] }}<br>
                    <strong>Web:{{ $company['company_website'] }}</strong>
                </div>

                <div class="emca-invoice-to-block">
                    <div class="emca-section-label">INVOICE TO:</div>
                    <div class="emca-invoice-to">
                        <strong>{{ $invoice->church->name }}</strong><br>
                        @if($invoice->recipient_name && $invoice->recipient_name !== $invoice->church->name)
                            {{ $invoice->recipient_name }}<br>
                        @endif
                        @if($invoice->recipient_phone)
                            {{ $invoice->recipient_phone }}<br>
                        @endif
                        @if($invoice->recipient_location)
                            <strong>{{ $invoice->recipient_location }}</strong>
                        @endif
                    </div>
                </div>
            </td>
            <td class="emca-col-right">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="EmCa" class="emca-logo">
                @endif

                <div class="emca-meta">
                    <div><strong>Invoice#:</strong>&nbsp;&nbsp;{{ $invoice->invoice_number }}</div>
                    <div><strong>Date:</strong> {{ $issuedDate }}</div>
                </div>

                <div class="emca-remarks">
                    <strong>Remarks / Payment Instructions:</strong><br>
                    The payment can be made direct to {{ $bankParts[0] ?: $bankName }}<br>
                    @if(! empty($bankParts[1]))
                        {{ $bankParts[1] }} with the following details:<br>
                    @else
                        with the following details:<br>
                    @endif
                    <span class="emca-accent">{{ $company['bank_account_name'] }}</span><br>
                    <span class="emca-accent">A/C no.{{ $company['bank_account_number'] }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="emca-purpose">
        <strong>Purpose:</strong> {{ $company['invoice_purpose'] }}
    </div>

    <table class="emca-items">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Qnty</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $invoice->description }}</td>
                <td>{{ $invoice->quantity }}</td>
                <td>{{ number_format($invoice->unit_price, 0) }}/=</td>
                <td>{{ number_format($invoice->subtotal, 0) }}/=</td>
            </tr>
        </tbody>
    </table>

    <table class="emca-totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">{{ number_format($invoice->subtotal, 0) }}/=</td>
        </tr>
        <tr>
            <td class="label">Tax ({{ rtrim(rtrim(number_format($invoice->tax_rate, 2, '.', ''), '0'), '.') }} %)</td>
            <td class="value">{{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Total Price</td>
            <td class="value">{{ number_format($invoice->total_amount, 0) }}/=</td>
        </tr>
    </table>

    <div class="emca-footer">
        The invoice is quoted in {{ $currencyLabel }}. Share your receipt after payment.
    </div>
</div>
