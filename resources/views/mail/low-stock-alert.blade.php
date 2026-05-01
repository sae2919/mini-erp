<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; }
        .card { background: #fff; border-radius: 8px; max-width: 600px; margin: 0 auto; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #dc2626; color: #fff; padding: 24px 28px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 6px 0 0; opacity: 0.85; font-size: 14px; }
        .body { padding: 24px 28px; }
        .body p { color: #374151; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th { background: #f9fafb; text-align: left; padding: 10px 12px; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-out  { background: #fee2e2; color: #dc2626; }
        .badge-low  { background: #fef9c3; color: #ca8a04; }
        .footer { background: #f9fafb; padding: 16px 28px; font-size: 12px; color: #9ca3af; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600; margin-top: 12px; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>⚠️ Low Stock Alert</h1>
        <p>{{ $products->count() }} product(s) need restocking</p>
    </div>
    <div class="body">
        <p>Hello Admin,</p>
        <p>The following products have fallen below their minimum stock threshold and require attention:</p>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Threshold</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td style="font-family: monospace; font-size: 12px; color: #6b7280;">{{ $product->sku }}</td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td><strong>{{ $product->stock_quantity }} {{ $product->unit }}</strong></td>
                    <td>{{ $product->low_stock_threshold }}</td>
                    <td>
                        @if($product->stock_quantity == 0)
                            <span class="badge badge-out">Out of Stock</span>
                        @else
                            <span class="badge badge-low">Low Stock</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>Please create purchase orders to restock these items.</p>
        <a href="{{ url('/purchases/create') }}" class="btn">Create Purchase Order</a>
    </div>
    <div class="footer">
        This alert was sent automatically at {{ now()->format('d M Y, h:i A') }} by {{ config('app.name') }}.
    </div>
</div>
</body>
</html>
