<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Low Stock Alert</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f6f8fa;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 14px;
            color: #24292f;
            line-height: 1.6;
        }

        .wrapper {
            max-width: 620px;
            margin: 32px auto;
            background: #ffffff;
            border: 1px solid #d0d7de;
            border-radius: 8px;
            overflow: hidden;
        }

        /* Header */
        .header {
            background: #1c2230;
            padding: 28px 32px;
            text-align: center;
        }

        .header-eyebrow {
            font-size: 10px;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #58a6ff;
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
        }

        .header-title {
            font-size: 22px;
            font-weight: 700;
            color: #f0f6fc;
            letter-spacing: -0.02em;
        }

        .header-subtitle {
            font-size: 12px;
            color: #6e7681;
            margin-top: 6px;
            font-family: 'Courier New', monospace;
        }

        /* Alert banner */
        .alert-banner {
            background: #fff0f0;
            border-bottom: 1px solid #ffc1c1;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #cf222e;
            font-weight: 500;
        }

        /* Body */
        .body {
            padding: 28px 32px;
        }

        .intro {
            font-size: 14px;
            color: #24292f;
            margin-bottom: 24px;
            line-height: 1.7;
        }

        .intro strong { color: #cf222e; }

        /* Section label */
        .section-label {
            font-size: 10px;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #6e7681;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #d0d7de;
        }

        /* Items table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }

        thead {
            background: #f6f8fa;
        }

        th {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #6e7681;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #d0d7de;
        }

        td {
            padding: 11px 12px;
            border-bottom: 1px solid #eef1f5;
            font-size: 13px;
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }

        .item-name {
            font-weight: 600;
            color: #1a1e22;
        }

        .item-category {
            font-size: 10px;
            font-family: 'Courier New', monospace;
            color: #0969da;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .qty-critical {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 700;
            color: #cf222e;
        }

        .qty-low {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: 700;
            color: #9a6700;
        }

        .threshold {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #9198a1;
        }

        .badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            border: 1px solid;
        }

        .badge-critical {
            background: #fff0f0;
            color: #cf222e;
            border-color: #ffc1c1;
        }

        .badge-low {
            background: #fff8e1;
            color: #9a6700;
            border-color: #ffe082;
        }

        /* CTA Button */
        .cta-wrap {
            text-align: center;
            margin: 8px 0 28px;
        }

        .cta-btn {
            display: inline-block;
            background: #0969da;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 11px 28px;
            border-radius: 6px;
            letter-spacing: -0.01em;
        }

        /* Footer */
        .footer {
            background: #f6f8fa;
            border-top: 1px solid #d0d7de;
            padding: 18px 32px;
            text-align: center;
            font-size: 11px;
            color: #9198a1;
            font-family: 'Courier New', monospace;
            line-height: 1.8;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <div class="header-eyebrow">// inventory-hub</div>
        <div class="header-title">⚠ Low Stock Alert</div>
        <div class="header-subtitle">
            {{ $isManual ? 'Manual check triggered' : 'Automated daily check' }}
            &nbsp;·&nbsp; {{ now()->format('M d, Y H:i') }}
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="alert-banner">
        ⚠ &nbsp;
        <strong>{{ $items->count() }} item{{ $items->count() > 1 ? 's' : '' }}</strong>
        &nbsp;{{ $items->count() > 1 ? 'are' : 'is' }} at or below the restock threshold.
    </div>

    <!-- Body -->
    <div class="body">

        <p class="intro">
            The following inventory items require immediate attention.
            @if($isManual)
                This alert was <strong>manually triggered</strong> by an administrator.
            @else
                This is an <strong>automated daily alert</strong> from Inventory Hub.
            @endif
            Please restock these items as soon as possible to avoid disruptions.
        </p>

        <div class="section-label">// Items Requiring Restock</div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Threshold</th>
                    <th>Deficit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items->sortBy('quantity') as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        <div class="item-category">{{ $item->category }}</div>
                    </td>
                    <td>
                        @if($item->quantity <= 0)
                            <span class="qty-critical">0</span>
                        @else
                            <span class="qty-low">{{ $item->quantity }}</span>
                        @endif
                    </td>
                    <td class="threshold">≤ {{ $item->low_stock_threshold }}</td>
                    <td class="threshold" style="color: #cf222e; font-weight:600;">
                        −{{ $item->stockDeficit() }}
                    </td>
                    <td>
                        @if($item->quantity <= 0)
                            <span class="badge badge-critical">Out of Stock</span>
                        @else
                            <span class="badge badge-low">Low Stock</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cta-wrap">
            <a href="{{ url('/inventory/low-stock') }}" class="cta-btn">
                View Low Stock Items →
            </a>
        </div>

    </div>

    <!-- Footer -->
    <div class="footer">
        This email was sent by Inventory Hub.<br>
        {{ now()->format('Y') }} · Automated alert system ·
        {{ $isManual ? 'Manually triggered' : 'Scheduled daily at 08:00' }}
    </div>

</div>
</body>
</html>
