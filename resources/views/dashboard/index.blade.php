<x-layout title="Analytics Dashboard">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// admin / dashboard</div>
            <h1 class="page-title">Analytics Dashboard</h1>
            <div class="page-subtitle">
                {{ $from->format('M d, Y') }} — {{ $to->format('M d, Y') }}
            </div>
        </div>

        {{-- Export buttons --}}
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('dashboard.export', request()->query()) }}"
               class="btn btn-secondary" title="Export transactions for selected period">
                ↓ Transactions (.xlsx)
            </a>
            <a href="{{ route('dashboard.export.snapshot') }}"
               class="btn btn-secondary" title="Export current stock levels">
                ↓ Stock Snapshot (.xlsx)
            </a>
        </div>
    </div>

    {{-- ── Date Range Filter ──────────────────────────────────── --}}
    <form method="GET" action="{{ route('dashboard.index') }}" id="filter-form">
        <div class="toolbar" style="margin-bottom:28px;">
            <div class="toolbar-left" style="flex-wrap:wrap; gap:6px;">

                @foreach([
                    'today' => 'Today',
                    '7d'    => 'Last 7 Days',
                    '30d'   => 'Last 30 Days',
                    '90d'   => 'Last 90 Days',
                    'year'  => 'This Year',
                    'custom'=> 'Custom',
                ] as $value => $label)
                    <button type="submit" name="range" value="{{ $value }}"
                        class="btn btn-sm {{ $preset === $value ? 'btn-primary' : 'btn-secondary' }}">
                        {{ $label }}
                    </button>
                @endforeach

            </div>

            {{-- Custom date inputs --}}
            <div class="toolbar-right" id="custom-range"
                 style="{{ $preset === 'custom' ? 'display:flex' : 'display:none' }}; gap:8px; align-items:center;">
                <input type="date" name="from" class="form-control" style="width:150px; padding:7px 10px;"
                       value="{{ $preset === 'custom' ? $from->toDateString() : '' }}" />
                <span style="color:var(--text-dim); font-size:12px;">to</span>
                <input type="date" name="to" class="form-control" style="width:150px; padding:7px 10px;"
                       value="{{ $preset === 'custom' ? $to->toDateString() : '' }}" />
                <input type="hidden" name="range" value="custom" />
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
        </div>
    </form>

    {{-- ── KPI Cards ──────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:14px; margin-bottom:28px;">

        <div class="detail-card" style="padding:20px;">
            <div class="kpi-label">Total Items</div>
            <div class="kpi-value">{{ number_format($totalItems) }}</div>
            <div class="kpi-sub">{{ $totalCategories }} categories</div>
        </div>

        <div class="detail-card" style="padding:20px;">
            <div class="kpi-label">Total Stock</div>
            <div class="kpi-value">{{ number_format($totalStock) }}</div>
            <div class="kpi-sub">units across all items</div>
        </div>

        <div class="detail-card" style="padding:20px; {{ $lowStockCount > 0 ? 'border-color:var(--red-border);' : '' }}">
            <div class="kpi-label">Low Stock</div>
            <div class="kpi-value" style="{{ $lowStockCount > 0 ? 'color:var(--red);' : '' }}">
                {{ $lowStockCount }}
            </div>
            <div class="kpi-sub">
                @if($lowStockCount > 0)
                    <a href="{{ route('inventory.low-stock') }}" style="color:var(--red);">View items →</a>
                @else
                    All levels healthy
                @endif
            </div>
        </div>

        <div class="detail-card" style="padding:20px; border-color:var(--green-border);">
            <div class="kpi-label">Stocked In</div>
            <div class="kpi-value" style="color:var(--green);">+{{ number_format($stockInPeriod) }}</div>
            <div class="kpi-sub">units in period</div>
        </div>

        <div class="detail-card" style="padding:20px; border-color:var(--red-border);">
            <div class="kpi-label">Stocked Out</div>
            <div class="kpi-value" style="color:var(--red);">−{{ number_format($stockOutPeriod) }}</div>
            <div class="kpi-sub">units in period</div>
        </div>

        <div class="detail-card" style="padding:20px;">
            <div class="kpi-label">Transactions</div>
            <div class="kpi-value" style="color:var(--accent);">{{ number_format($txCountPeriod) }}</div>
            <div class="kpi-sub">in selected period</div>
        </div>

    </div>

    {{-- ── Charts Row ─────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:20px;">

        <div class="detail-card">
            <div class="detail-card-title">// Stock Movement — Daily</div>
            <div style="position:relative; height:260px;">
                <canvas id="movementChart"></canvas>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-card-title">// Item Status Distribution</div>
            <div style="position:relative; height:260px; display:flex; align-items:center; justify-content:center;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

    </div>

    <div class="detail-card" style="margin-bottom:20px;">
        <div class="detail-card-title">// Inventory by Category</div>
        <div style="position:relative; height:220px;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    {{-- ── Bottom Grid ────────────────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

        <div class="detail-card">
            <div class="detail-card-title">// Most Active Items (Period)</div>
            @if($topItems->count())
                @foreach($topItems as $row)
                <div style="display:flex; align-items:center; justify-content:space-between;
                            padding:9px 0; border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-size:13px; font-weight:600; color:var(--text-hi);">
                            <a href="{{ route('inventory.show', ['inventory' => $row->inventory_item_id]) }}"
                               style="color:var(--text-hi);">
                                {{ $row->item->name ?? 'Deleted item' }}
                            </a>
                        </div>
                        <div style="font-family:var(--mono); font-size:10px; color:var(--accent); text-transform:uppercase; letter-spacing:0.05em;">
                            {{ $row->item->category ?? '—' }}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-family:var(--mono); font-size:13px; font-weight:700; color:var(--text-hi);">
                            {{ $row->tx_count }} tx
                        </div>
                        <div style="font-family:var(--mono); font-size:11px; color:var(--text-dim);">
                            {{ $row->total_moved }} units
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div style="text-align:center; padding:40px 0; color:var(--text-muted); font-size:13px;">
                    No transactions in this period.
                </div>
            @endif
        </div>

        <div class="detail-card">
            <div class="detail-card-title">// Low Stock Items</div>
            @if($lowStockItems->count())
                @foreach($lowStockItems as $item)
                <div style="display:flex; align-items:center; justify-content:space-between;
                            padding:9px 0; border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-size:13px; font-weight:600; color:var(--text-hi);">
                            <a href="{{ route('inventory.show', ['inventory' => $item->id]) }}"
                               style="color:var(--text-hi);">
                                {{ $item->name }}
                            </a>
                        </div>
                        <div style="font-family:var(--mono); font-size:10px; color:var(--accent); text-transform:uppercase; letter-spacing:0.05em;">
                            {{ $item->category }}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-family:var(--mono); font-size:14px; font-weight:800; color:var(--red);">
                            {{ $item->quantity }}
                        </div>
                        <div style="font-family:var(--mono); font-size:10px; color:var(--text-dim);">
                            threshold: {{ $item->low_stock_threshold }}
                        </div>
                    </div>
                </div>
                @endforeach
                @if($lowStockCount > 8)
                    <div style="margin-top:12px; text-align:center;">
                        <a href="{{ route('inventory.low-stock') }}" style="font-size:12px; color:var(--accent);">
                            View all {{ $lowStockCount }} low stock items →
                        </a>
                    </div>
                @endif
            @else
                <div style="text-align:center; padding:40px 0; color:var(--text-muted); font-size:13px;">
                    ✓ All stock levels are healthy.
                </div>
            @endif
        </div>

    </div>

    {{-- ── Recent Transactions ─────────────────────────────────── --}}
    <div class="detail-card" style="margin-bottom:20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
            <div class="detail-card-title" style="margin-bottom:0; padding-bottom:0; border:none;">
                // Recent Transactions
            </div>
            <a href="{{ route('dashboard.export', request()->query()) }}"
               style="font-family:var(--mono); font-size:11px; color:var(--accent);">
                ↓ Export period →
            </a>
        </div>
        <div class="table-wrap" style="box-shadow:none;">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Before → After</th>
                        <th>Reason</th>
                        <th>Staff</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $tx)
                    <tr>
                        <td class="text-mono" style="font-size:11px; color:var(--text-dim); white-space:nowrap;">
                            {{ $tx->created_at->format('M d') }}<br>
                            <span style="color:var(--text-muted);">{{ $tx->created_at->format('H:i') }}</span>
                        </td>
                        <td>
                            <div style="font-size:13px; font-weight:600; color:var(--text-hi);">
                                <a href="{{ route('inventory.show', ['inventory' => $tx->inventory_item_id]) }}"
                                   style="color:var(--text-hi);">
                                    {{ $tx->item->name ?? 'Deleted item' }}
                                </a>
                            </div>
                            <div style="font-family:var(--mono); font-size:10px; color:var(--accent); text-transform:uppercase;">
                                {{ $tx->item->category ?? '—' }}
                            </div>
                        </td>
                        <td>
                            @if($tx->isStockIn())
                                <span class="badge badge-available">↑ In</span>
                            @else
                                <span class="badge badge-maintenance">↓ Out</span>
                            @endif
                        </td>
                        <td style="font-family:var(--mono); font-weight:700; font-size:13px;
                                   color:{{ $tx->isStockIn() ? 'var(--green)' : 'var(--red)' }};">
                            {{ $tx->isStockIn() ? '+' : '−' }}{{ $tx->quantity }}
                        </td>
                        <td style="font-family:var(--mono); font-size:12px; color:var(--text-dim);">
                            {{ $tx->quantity_before }} → {{ $tx->quantity_after }}
                        </td>
                        <td style="font-size:12px; max-width:160px;">{{ $tx->reason }}</td>
                        <td style="font-size:12px; color:var(--text-dim);">{{ $tx->user->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<script>
    // ── Show/hide custom date inputs ──────────────────────────
    document.querySelectorAll('button[name="range"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const customRange = document.getElementById('custom-range');
            customRange.style.display = btn.value === 'custom' ? 'flex' : 'none';
        });
    });

    // ── Shared chart config ───────────────────────────────────
    const style    = getComputedStyle(document.documentElement);
    const textDim  = style.getPropertyValue('--text-dim').trim()  || '#6e7681';
    const border   = style.getPropertyValue('--border').trim()    || '#21262d';
    const accent   = style.getPropertyValue('--accent').trim()    || '#58a6ff';
    const green    = style.getPropertyValue('--green').trim()     || '#3fb950';
    const red      = style.getPropertyValue('--red').trim()       || '#f85149';
    const amber    = style.getPropertyValue('--amber').trim()     || '#d29922';

    Chart.defaults.color       = textDim;
    Chart.defaults.borderColor = border;
    Chart.defaults.font.family = "'IBM Plex Mono', monospace";
    Chart.defaults.font.size   = 11;

    // ── Daily Movement Chart ──────────────────────────────────
    new Chart(document.getElementById('movementChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'Stock In',
                    data: @json($chartIn),
                    backgroundColor: 'rgba(63, 185, 80, 0.7)',
                    borderColor: green,
                    borderWidth: 1,
                    borderRadius: 3,
                },
                {
                    label: 'Stock Out',
                    data: @json($chartOut),
                    backgroundColor: 'rgba(248, 81, 73, 0.7)',
                    borderColor: red,
                    borderWidth: 1,
                    borderRadius: 3,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } } },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 14 } },
                y: { beginAtZero: true, grid: { color: border } },
            },
        }
    });

    // ── Status Doughnut ───────────────────────────────────────
    const statusData   = @json($statusStats);
    const statusLabels = Object.keys(statusData).map(s => s.replace('_', ' ').toUpperCase());
    const statusValues = Object.values(statusData);
    const statusColors = statusLabels.map(l =>
        l.includes('AVAILABLE')   ? 'rgba(63,185,80,0.8)'  :
        l.includes('OUT')         ? 'rgba(210,153,34,0.8)' :
        l.includes('MAINTENANCE') ? 'rgba(248,81,73,0.8)'  : 'rgba(88,166,255,0.8)'
    );

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusValues,
                backgroundColor: statusColors,
                borderColor: style.getPropertyValue('--surface').trim(),
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 16, boxWidth: 12 } } },
            cutout: '65%',
        }
    });

    // ── Category Bar Chart ────────────────────────────────────
    const catData = @json($categoryStats);

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: catData.map(c => c.category.toUpperCase()),
            datasets: [
                {
                    label: 'Item Count',
                    data: catData.map(c => c.item_count),
                    backgroundColor: 'rgba(88,166,255,0.7)',
                    borderColor: accent,
                    borderWidth: 1,
                    borderRadius: 3,
                    yAxisID: 'y',
                },
                {
                    label: 'Total Qty',
                    data: catData.map(c => c.total_qty),
                    backgroundColor: 'rgba(210,153,34,0.6)',
                    borderColor: amber,
                    borderWidth: 1,
                    borderRadius: 3,
                    yAxisID: 'y1',
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 16 } } },
            scales: {
                x:  { grid: { display: false } },
                y:  { beginAtZero: true, position: 'left',  title: { display: true, text: 'Items' } },
                y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Units' }, grid: { drawOnChartArea: false } },
            },
        }
    });
</script>
