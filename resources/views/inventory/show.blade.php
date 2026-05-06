<x-layout title="{{ $item->name }}">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// inventory / #{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</div>
            <h1 class="page-title">{{ $item->name }}</h1>
            <div class="page-subtitle">{{ ucfirst($item->category) }}</div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            {{-- Stock In / Out — available to all authenticated users --}}
            <a href="{{ route('inventory.transactions.create', ['inventory' => $item->id, 'type' => 'in']) }}"
               class="btn" style="background:var(--green); color:#fff; border-color:var(--green);">
                ↑ Stock In
            </a>
            <a href="{{ route('inventory.transactions.create', ['inventory' => $item->id, 'type' => 'out']) }}"
               class="btn btn-danger">
                ↓ Stock Out
            </a>
            <a href="{{ route('inventory.transactions.index', ['inventory' => $item->id]) }}"
               class="btn btn-secondary">
                📋 Ledger
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('inventory.edit', ['inventory' => $item->id]) }}" class="btn btn-secondary">✎ Edit</a>
                <button class="btn btn-danger"
                    onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ route('inventory.destroy', ['inventory' => $item->id]) }}')">
                    ✕ Delete
                </button>
            @endif
        </div>
    </div>

    @if($item->isLowStock())
        <div class="alert alert-error mb-4">
            ⚠ &nbsp;<strong>Low Stock:</strong> Only {{ $item->quantity }} unit(s) remaining
            (threshold: {{ $item->low_stock_threshold }}).
            Consider restocking soon.
        </div>
    @endif

    <div class="detail-grid">

        <!-- Core Info -->
        <div class="detail-card">
            <div class="detail-card-title">// Core Information</div>
            <div class="detail-row">
                <span class="detail-key">ID</span>
                <span class="detail-val">#{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Name</span>
                <span class="detail-val">{{ $item->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Category</span>
                <span class="detail-val" style="color:var(--accent); text-transform:uppercase; font-size:11px; letter-spacing:0.06em;">
                    {{ $item->category }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Status</span>
                <span class="detail-val">
                    <span class="badge badge-{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Location</span>
                <span class="detail-val">{{ $item->attributes['location'] ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Created</span>
                <span class="detail-val">{{ $item->created_at->format('M d, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Updated</span>
                <span class="detail-val">{{ $item->updated_at->diffForHumans() }}</span>
            </div>
        </div>

        <!-- Stock Info -->
        <div class="detail-card">
            <div class="detail-card-title">// Stock Information</div>
            <div class="detail-row">
                <span class="detail-key">Quantity</span>
                <span class="detail-val">
                    <span class="{{ $item->isLowStock() ? 'qty-low' : '' }}"
                          style="font-size:22px; font-weight:800; font-family:var(--mono);">
                        {{ $item->quantity }}
                    </span>
                    @if($item->isLowStock())
                        <span style="color:var(--red); font-size:11px; margin-left:6px;">⚠ Low</span>
                    @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Low Stock At</span>
                <span class="detail-val">≤ {{ $item->low_stock_threshold }} units</span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Stock Status</span>
                <span class="detail-val">
                    @if($item->isLowStock())
                        <span class="badge badge-maintenance">Low Stock</span>
                    @else
                        <span class="badge badge-available">Sufficient</span>
                    @endif
                </span>
            </div>
            @if($item->isLowStock())
            <div class="detail-row">
                <span class="detail-key">Deficit</span>
                <span class="detail-val" style="color:var(--red);">
                    {{ $item->stockDeficit() }} unit(s) below threshold
                </span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-key">Transactions</span>
                <span class="detail-val">
                    <a href="{{ route('inventory.transactions.index', ['inventory' => $item->id]) }}"
                       style="font-family:var(--mono); font-size:12px;">
                        View ledger →
                    </a>
                </span>
            </div>
        </div>

        {{-- Custom Attributes --}}
        @php
            $reserved    = ['status', 'location'];
            $customAttrs = array_filter(
                $item->attributes ?? [],
                fn($k) => !in_array($k, $reserved),
                ARRAY_FILTER_USE_KEY
            );
        @endphp

        @if(count($customAttrs))
        <div class="detail-card" style="grid-column: 1 / -1;">
            <div class="detail-card-title">// Custom Attributes</div>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0 24px;">
                @foreach($customAttrs as $key => $value)
                    <div class="detail-row">
                        <span class="detail-key">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="detail-val">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Recent Transactions Preview --}}
    @if($item->transactions()->exists())
    <div class="form-card" style="margin-top:0;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
            <div class="form-card-title" style="margin-bottom:0; padding-bottom:0; border-bottom:none;">
                // Recent Transactions
            </div>
            <a href="{{ route('inventory.transactions.index', ['inventory' => $item->id]) }}"
               style="font-family:var(--mono); font-size:11px; color:var(--accent);">
                View all →
            </a>
        </div>
        <div class="table-wrap" style="box-shadow:none; border:1px solid var(--border);">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Reason</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item->transactions()->with('user')->latest()->limit(5)->get() as $tx)
                    <tr>
                        <td class="text-mono" style="font-size:11px; color:var(--text-dim);">
                            {{ $tx->created_at->diffForHumans() }}
                        </td>
                        <td>
                            @if($tx->isStockIn())
                                <span class="badge badge-available">↑ In</span>
                            @else
                                <span class="badge badge-maintenance">↓ Out</span>
                            @endif
                        </td>
                        <td style="font-family:var(--mono); font-weight:700;
                                   color:{{ $tx->isStockIn() ? 'var(--green)' : 'var(--red)' }};">
                            {{ $tx->isStockIn() ? '+' : '−' }}{{ $tx->quantity }}
                        </td>
                        <td style="font-size:13px;">{{ $tx->reason }}</td>
                        <td style="font-size:12px; color:var(--text-dim);">{{ $tx->user->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <a href="{{ route('inventory.index') }}" class="btn btn-ghost">← Back to All Items</a>

</x-layout>
