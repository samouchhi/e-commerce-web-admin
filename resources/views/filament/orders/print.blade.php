<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isReceipt ? 'Receipt' : 'Invoice' }} — {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eeefec; color: #242a24; font: 14px/1.6 Arial, 'Khmer OS Battambang', sans-serif; }
        .toolbar { max-width: 820px; margin: 20px auto; display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 0 16px; }
        .toolbar a { color: #42583c; } button { border: 0; border-radius: 6px; background: #42583c; color: white; padding: 10px 18px; font: inherit; cursor: pointer; }
        button:focus-visible, a:focus-visible { outline: 3px solid #89a780; outline-offset: 3px; }
        .sheet { max-width: 820px; margin: 0 auto 32px; padding: 44px; background: white; border-top: 5px solid #42583c; }
        header, .details { display: flex; justify-content: space-between; gap: 32px; }
        header { padding-bottom: 28px; border-bottom: 1px solid #dce1d9; }
        h1 { font: 36px/1.2 Georgia, serif; margin: 0 0 8px; } h2 { font-size: 20px; margin: 0 0 6px; }
        p { margin: 2px 0; } .muted { color: #657064; } .label { font-size: 11px; text-transform: uppercase; letter-spacing: .12em; color: #657064; margin-bottom: 8px; }
        .document { text-align: right; } .reference { font-weight: bold; overflow-wrap: anywhere; }
        .logo { display: block; max-width: 160px; height: 64px; object-fit: contain; object-position: left center; margin-bottom: 12px; }
        .status { display: inline-block; padding: 3px 10px; border: 1px solid #aebba8; border-radius: 4px; font-size: 12px; margin-top: 10px; }
        .details { padding: 24px 0; } .details > div { flex: 1; min-width: 0; } .details p, .store p { overflow-wrap: anywhere; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { padding: 10px 8px; border-block: 1px solid #dce1d9; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; text-align: left; }
        td { padding: 12px 8px; border-bottom: 1px solid #e8ebe6; vertical-align: top; overflow-wrap: anywhere; }
        .number { text-align: right; font-variant-numeric: tabular-nums; } .item-name { font-weight: bold; } .variant { font-size: 12px; color: #657064; }
        .totals { width: 310px; max-width: 100%; margin: 20px 0 28px auto; }
        .totals div { display: flex; justify-content: space-between; gap: 20px; padding: 5px 0; }
        .totals .grand { margin-top: 8px; padding-top: 12px; border-top: 2px solid #42583c; font-size: 18px; font-weight: bold; }
        .note { margin-bottom: 20px; white-space: pre-line; overflow-wrap: anywhere; }
        @page { size: A4; margin: 14mm; }
        @media print {
            body { background: white; font-size: 11pt; } .toolbar { display: none; }
            .sheet { max-width: none; margin: 0; padding: 0; border: 0; }
            header, .details, tr, .totals { break-inside: avoid; }
            thead { display: table-header-group; } .totals { break-before: auto; }
        }
        @media screen and (max-width: 600px) {
            .sheet { padding: 24px 16px; } header, .details { flex-direction: column; gap: 20px; }
            .document { text-align: left; } h1 { font-size: 30px; } th, td { padding-inline: 4px; font-size: 12px; }
        }
    </style>
</head>
<body>
    <nav class="toolbar" aria-label="Document actions">
        <a href="{{ route('filament.dashboard.resources.orders.index') }}">Back to orders</a>
        <button type="button" onclick="window.print()">Print / Save PDF</button>
    </nav>
    <main class="sheet">
        <header>
            <div class="store">
                @if ($logoUrl)<img class="logo" src="{{ $logoUrl }}" alt="{{ $settings?->site_name ?: config('app.name') }}">@endif
                <h2>{{ $settings?->site_name ?: config('app.name') }}</h2>
                @if ($settings?->site_address)<p class="muted">{{ $settings->site_address }}</p>@endif
                @if ($settings?->site_phone)<p class="muted">{{ $settings->site_phone }}</p>@endif
                @if ($settings?->site_email)<p class="muted">{{ $settings->site_email }}</p>@endif
            </div>
            <div class="document">
                <h1>{{ $isReceipt ? 'Receipt' : 'Invoice' }}</h1>
                <p class="reference">{{ $order->order_number }}</p>
                <p class="muted">Order date · {{ $order->created_at?->format('d M Y') }}</p>
                <span class="status">{{ $isReceipt ? 'Paid in full' : $order->payment_status->getLabel().' · Unpaid' }}</span>
            </div>
        </header>
        <section class="details" aria-label="Customer and delivery details">
            <div>
                <p class="label">{{ $isReceipt ? 'Received from' : 'Bill to' }}</p>
                <p><strong>{{ $order->customer?->name ?: 'Customer #'.$order->customer_id }}</strong></p>
                @if ($order->customer?->email)<p>{{ $order->customer->email }}</p>@endif
                @if ($order->address?->phone ?: $order->customer?->phone)<p>{{ $order->address?->phone ?: $order->customer?->phone }}</p>@endif
            </div>
            <div>
                <p class="label">Delivery</p>
                <p>{{ $order->address?->address ?: 'Address not provided' }}</p>
                @if ($order->address?->city)<p>{{ $order->address->city }}</p>@endif
                @if ($order->logistic?->name)<p class="muted">{{ $order->logistic->name }}</p>@endif
            </div>
        </section>
        <table aria-label="Order items">
            <colgroup><col style="width: 46%"><col style="width: 10%"><col style="width: 22%"><col style="width: 22%"></colgroup>
            <thead><tr><th scope="col">Item</th><th scope="col" class="number">Qty</th><th scope="col" class="number">Unit price</th><th scope="col" class="number">Amount</th></tr></thead>
            <tbody>
                @forelse ($order->items as $item)
                    <tr>
                        <td><div class="item-name">{{ $item->productVariant?->product?->name ?: 'Item #'.$item->product_variant_id }}</div><div class="variant">{{ $item->productVariant?->name }}</div></td>
                        <td class="number">{{ $item->quantity }}</td>
                        <td class="number">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="number">{{ number_format((float) $item->subtotal_price, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No item details available for this order.</td></tr>
                @endforelse
            </tbody>
        </table>
        <section class="totals" aria-label="Order totals">
            <div><span>Subtotal</span><span>{{ number_format((float) $order->subtotal_amount, 2) }}</span></div>
            <div><span>Shipping</span><span>{{ number_format((float) $order->shipping_cost, 2) }}</span></div>
            <div class="grand"><span>Total · {{ $currency }}</span><span>{{ number_format((float) $order->total_amount, 2) }}</span></div>
            <div><span>{{ $isReceipt ? 'Amount paid' : 'Amount due' }}</span><strong>{{ $currency }} {{ number_format((float) $order->total_amount, 2) }}</strong></div>
            @if ($isReceipt && $order->payment?->qr_paid_at)<div><span>Paid on</span><span>{{ $order->payment->qr_paid_at->format('d M Y, H:i') }}</span></div>@endif
        </section>
        @if ($order->address?->note)<p class="note"><strong>Order note</strong><br>{{ $order->address->note }}</p>@endif
    </main>
</body>
</html>
