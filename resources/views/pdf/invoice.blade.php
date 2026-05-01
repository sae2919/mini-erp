<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $sale->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1f2937; background: #fff; }
        .page { padding: 40px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 2px solid #4f46e5; padding-bottom: 24px; }
        .company-name { font-size: 24px; font-weight: 700; color: #4f46e5; }
        .company-sub { font-size: 12px; color: #6b7280; margin-top: 4px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 28px; font-weight: 700; color: #1f2937; letter-spacing: 2px; }
        .invoice-title .ref { font-size: 14px; color: #4f46e5; font-weight: 600; margin-top: 4px; }
        .invoice-title .date { font-size: 12px; color: #6b7280; margin-top: 2px; }

        /* Meta grid */
        .meta { display: flex; justify-content: space-between; margin-bottom: 32px; gap: 24px; }
        .meta-box { flex: 1; background: #f9fafb; border-radius: 8px; padding: 16px; }
        .meta-box h3 { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .meta-box p { font-size: 13px; color: #374151; margin-bottom: 3px; }
        .meta-box .name { font-size: 15px; font-weight: 600; color: #111827; }

        /* Items table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { background: #4f46e5; color: #fff; }
        thead th { padding: 10px 14px; text-align: left; font-size: 12px; font-weight: 600; letter-spacing: 0.5px; }
        thead th.right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 10px 14px; font-size: 13px; }
        tbody td.right { text-align: right; }
        tbody td.muted { color: #6b7280; font-size: 12px; }

        /* Totals */
        .totals { display: flex; justify-content: flex-end; margin-bottom: 32px; }
        .totals-table { width: 280px; }
        .totals-table tr td { padding: 6px 0; font-size: 13px; }
        .totals-table tr td:first-child { color: #6b7280; }
        .totals-table tr td:last-child { text-align: right; font-weight: 500; }
        .totals-table .grand-total td { font-size: 16px; font-weight: 700; color: #4f46e5; border-top: 2px solid #e5e7eb; padding-top: 10px; }
        .profit-row td { color: #059669 !important; font-weight: 600; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; padding-top: 20px; display: flex; justify-content: space-between; }
        .footer-note { font-size: 11px; color: #9ca3af; }
        .status-badge { background: #dcfce7; color: #16a34a; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div>
            <div class="company-name">{{ config('app.name', 'Mini ERP') }}</div>
            <div class="company-sub">Inventory & Sales Management</div>
        </div>
        <div class="invoice-title">
            <h1>INVOICE</h1>
            <div class="ref">{{ $sale->reference }}</div>
            <div class="date">{{ $sale->sale_date->format('d F Y') }}</div>
        </div>
    </div>

    {{-- Bill To + Sale Info --}}
    <div class="meta">
        <div class="meta-box">
            <h3>Bill To</h3>
            <p class="name">{{ $sale->customer_display }}</p>
            @if($sale->customer)
                @if($sale->customer->phone)<p>📞 {{ $sale->customer->phone }}</p>@endif
                @if($sale->customer->email)<p>✉️ {{ $sale->customer->email }}</p>@endif
                @if($sale->customer->address)<p>{{ $sale->customer->address }}</p>@endif
            @endif
        </div>
        <div class="meta-box">
            <h3>Invoice Details</h3>
            <p><strong>Invoice No:</strong> {{ $sale->reference }}</p>
            <p><strong>Date:</strong> {{ $sale->sale_date->format('d M Y') }}</p>
            <p><strong>Items:</strong> {{ $sale->items->count() }}</p>
            @if($sale->notes)
                <p><strong>Notes:</strong> {{ $sale->notes }}</p>
            @endif
        </div>
    </div>

    {{-- Line Items --}}
    <table>
        <thead>
            <tr>
                <th style="width:40%">Product</th>
                <th>SKU</th>
                <th class="right">Unit Price</th>
                <th class="right">Qty</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>
                    {{ $item->product->name }}
                    <br><span class="muted">{{ $item->product->category->name ?? '' }}</span>
                </td>
                <td class="muted">{{ $item->product->sku }}</td>
                <td class="right">₹{{ number_format($item->selling_price, 2) }}</td>
                <td class="right">{{ $item->quantity }} {{ $item->product->unit }}</td>
                <td class="right">₹{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td>₹{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Tax (0%)</td>
                <td>₹0.00</td>
            </tr>
            <tr class="grand-total">
                <td>Total</td>
                <td>₹{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-note">
            Thank you for your business.<br>
            Generated on {{ now()->format('d M Y, h:i A') }}
        </div>
        <div class="status-badge">✓ PAID</div>
    </div>

</div>
</body>
</html>
