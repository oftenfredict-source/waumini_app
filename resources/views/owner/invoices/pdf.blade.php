<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 22px 26px; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #000000;
            margin: 0;
            padding: 0;
        }
        .emca-invoice { width: 100%; }
        .emca-main {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .emca-col-left {
            width: 50%;
            vertical-align: top;
            padding: 0 18px 0 0;
        }
        .emca-header-left {
            margin: 0;
            padding: 0;
        }
        .emca-invoice-title {
            font-size: 54px;
            font-weight: bold;
            color: #940000;
            margin: 0;
            padding: 0;
            line-height: 1;
        }
        .emca-title-line {
            width: 210px;
            margin: 8px 0 14px 0;
            border-collapse: collapse;
        }
        .emca-title-line td {
            background-color: #940000;
            height: 6px;
            line-height: 6px;
            font-size: 1px;
            padding: 0;
        }
        .emca-col-right {
            width: 50%;
            vertical-align: top;
            padding: 0;
            text-align: right;
        }
        .emca-logo {
            height: 170px;
            width: auto;
            display: inline-block;
            margin-bottom: 6px;
        }
        .emca-company {
            line-height: 1.35;
            margin-bottom: 0;
        }
        .emca-invoice-to-block {
            margin-top: 48px;
        }
        .emca-section-label {
            font-weight: bold;
            margin: 0 0 4px;
            color: #000000;
        }
        .emca-invoice-to {
            line-height: 1.35;
        }
        .emca-meta {
            line-height: 1.45;
            margin-top: 4px;
            text-align: right;
        }
        .emca-remarks {
            line-height: 1.35;
            margin-top: 72px;
            text-align: right;
        }
        .emca-accent {
            color: #940000;
            font-weight: bold;
        }
        .emca-purpose {
            margin: 20px 0 12px;
            line-height: 1.35;
        }
        .emca-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .emca-items th,
        .emca-items td {
            border: 1px solid #940000;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
        }
        .emca-items th {
            background: #940000;
            color: #FFFFFF;
            font-weight: bold;
        }
        .emca-totals {
            width: 42%;
            margin-left: auto;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .emca-totals td {
            padding: 4px 0;
            vertical-align: top;
        }
        .emca-totals .label {
            text-align: left;
            font-weight: bold;
            width: 55%;
        }
        .emca-totals .value {
            text-align: right;
            width: 45%;
        }
        .emca-footer {
            margin-top: 18px;
            line-height: 1.35;
        }
    </style>
</head>
<body>
    @include('owner.invoices.partials.sample-layout', ['invoice' => $invoice, 'company' => $company])
</body>
</html>
