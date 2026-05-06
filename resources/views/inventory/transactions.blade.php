<x-layout title="Transaction Ledger — {{ $inventory->name }}">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// inventory / #{{ str_pad($inventory->id, 4, '0', STR_PAD_LEFT) }} / ledger</div>
            <h1 class="page-title">Transaction Ledger</h1>
            <div class="page-subtitle">{{ $inventory->name }}</div>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('inventory.transactions.create', ['inventory' => $inventory->id, 'type' => 'in']) }}"
               class="btn" style="background:var(--green); color:#fff; border-color:var(--green);">
                ↑ Stock In
            </a>
            <a href="{{ route('inventory.transactions.create', ['inventory' => $inventory->id, 'type' => 'out']) }}"
               class="btn btn-danger">
                ↓ Stock Out
            </a>
            <a href="{{ route('inventory.show', ['inventory' => $inventory->id]) }}" class="btn btn-secondary">← Item</a>
        </div>
    </div>

    {{-- Summary Strip --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:14px; margin-bottom:28px;">

        <div class="detail-card" style="padding:18px;">
            <div style="font-family:var(--mono); font-size:10px; letter-spacing:0.12em; text-transform:uppercase;
                        color:var(--text-dim); margin-bottom:6px;">Current Stock</div>
            <div style="font-size:28px; font-weight:800; font-family:var(--mono);
                        color:{{ $inventory->isLowStock() ? 'var(--red)' : 'var(--text-hi)' }}; line-height:1;">
                {{ $inventory->quantity }}
            </div>
        </div>

        <div class="detail-card" style="padding:18px; border-color:var(--green-border);">
            <div style="font-family:var(--mono); font-size:10px; letter-spacing:0.12em; text-transform:uppercase;
                        color:var(--text-dim); margin-bottom:6px;">Total Stocked In</div>
            <div style="font-size:28px; font-weight:800; font-family:var(--mono); color:var(--green); line-height:1;">
                +{{ $totalIn }}
            </div>
        </div>

        <div class="detail-card" style="padding:18px; border-color:var(--red-border);">
            <div style="font-family:var(--mono); font-size:10px; letter-spacing:0.12em; text-transform:uppercase;
                        color:var(--text-dim); margin-bottom:6px;">Total Stocked Out</div>
            <div style="font-size:28px; font-weight:800; font-family:var(--mono); color:var(--red); line-height:1;">
                −{{ $totalOut }}
            </div>
        </div>

        <div class="detail-card" style="padding:18px;">
            <div style="font-family:var(--mono); font-size:10px; letter-spacing:0.12em; text-transform:uppercase;
                        color:var(--text-dim); margin-bottom:6px;">Total Transactions</div>
            <div style="font-size:28px; font-weight:800; font-family:var(--mono); color:var(--accent); line-height:1;">
                {{ $transactions->total() }}
            </div>
        </div>

    </div>

    {{-- Ledger Table --}}
    @if($transactions->count())
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Reason</th>
                        <th>Notes</th>
                        <th>Staff</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                    <tr>
                        <td class="text-mono" style="font-size:11px; color:var(--text-dim); white-space:nowrap;">
                            {{ $tx->created_at->format('M d, Y') }}<br>
                            <span style="color:var(--text-muted);">{{ $tx->created_at->format('H:i:s') }}</span>
                        </td>
                        <td>
                            @if($tx->isStockIn())
                                <span class="badge badge-available">↑ In</span>
                            @else
                                <span class="badge badge-maintenance">↓ Out</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-family:var(--mono); font-size:14px; font-weight:700;
                                         color:{{ $tx->isStockIn() ? 'var(--green)' : 'var(--red)' }};">
                                {{ $tx->isStockIn() ? '+' : '−' }}{{ $tx->quantity }}
                            </span>
                        </td>
                        <td class="text-mono" style="font-size:12px; color:var(--text-dim);">
                            {{ $tx->quantity_before }}
                        </td>
                        <td class="text-mono" style="font-size:12px;
                            color:{{ $tx->quantity_after <= $inventory->low_stock_threshold ? 'var(--red)' : 'var(--text-hi)' }}; font-weight:600;">
                            {{ $tx->quantity_after }}
                        </td>
                        <td style="font-size:13px; max-width:200px;">{{ $tx->reason }}</td>
                        <td style="font-size:12px; color:var(--text-dim); max-width:180px;">
                            {{ $tx->notes ?? '—' }}
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:7px;">
                                <div class="user-avatar" style="width:24px; height:24px; font-size:9px; flex-shrink:0;">
                                    {{ strtoupper(substr($tx->user->name ?? '?', 0, 2)) }}
                                </div>
                                <span style="font-size:12px; color:var(--text-dim);">{{ $tx->user->name ?? 'Deleted user' }}</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }}</span>
            {{ $transactions->links('vendor.pagination.custom') }}
        </div>

    @else
        <div class="empty-state">
            <div class="empty-glyph">📋</div>
            <div class="empty-title">No transactions yet</div>
            <div class="empty-sub">Use Stock In or Stock Out to start tracking movement for this item.</div>
        </div>
    @endif

</x-layout>
