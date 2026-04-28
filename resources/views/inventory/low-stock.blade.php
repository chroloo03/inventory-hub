<x-layout title="Low Stock Items">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// inventory / alerts</div>
            <h1 class="page-title">Low Stock Items</h1>
            <div class="page-subtitle">
                {{ $items->total() }} item(s) at or below their restock threshold
            </div>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">← All Items</a>
    </div>

    @if($items->total() === 0 && !request()->hasAny(['search','category','status']))
        <div class="empty-state">
            <div class="empty-glyph">✓</div>
            <div class="empty-title">All stock levels are healthy</div>
            <div class="empty-sub">No items are currently below their restock threshold.</div>
            <a href="{{ route('inventory.index') }}" class="btn btn-primary">Back to Inventory</a>
        </div>
    @else

        <form method="GET" action="{{ route('inventory.low-stock') }}" id="filter-form">
            <div class="toolbar">
                <div class="toolbar-left">
                    <div class="search-wrap">
                        <span class="search-icon">⌕</span>
                        <input type="text" name="search" class="search-input"
                            placeholder="Search by name..."
                            value="{{ request('search') }}" autocomplete="off" />
                    </div>

                    <select name="category" class="filter-select" onchange="document.getElementById('filter-form').submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                {{ ucfirst($cat) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="status" class="filter-select" onchange="document.getElementById('filter-form').submit()">
                        <option value="">All Statuses</option>
                        <option value="available"   {{ request('status') === 'available'   ? 'selected' : '' }}>Available</option>
                        <option value="checked_out" {{ request('status') === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                        <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>

                    @if(request()->hasAny(['search','category','status']))
                        <a href="{{ route('inventory.low-stock') }}" class="btn btn-ghost btn-sm">✕ Clear</a>
                    @endif
                </div>
                <div class="toolbar-right">
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                </div>
            </div>
        </form>

        @if($items->count())
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Qty</th>
                            <th>Threshold</th>
                            <th>Deficit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr class="low-stock-row" style="cursor:pointer;"
                            onclick="window.location='{{ route('inventory.show', ['inventory' => $item->id]) }}'">
                            <td class="td-id">#{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="td-name">{{ $item->name }}</td>
                            <td class="td-category">{{ $item->category }}</td>
                            <td><span class="badge badge-{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span></td>
                            <td>
                                <span class="qty-cell qty-low">
                                    {{ $item->quantity }}
                                    <span class="qty-warn-icon">⚠</span>
                                </span>
                            </td>
                            <td class="text-mono" style="font-size:12px; color:var(--text-dim);">
                                {{ $item->low_stock_threshold }}
                            </td>
                            <td>
                                <span style="color:var(--red); font-family:var(--mono); font-size:12px; font-weight:600;">
                                    −{{ $item->stockDeficit() }}
                                </span>
                            </td>
                            <td onclick="event.stopPropagation()">
                                <div class="td-actions">
                                    <a href="{{ route('inventory.show', ['inventory' => $item->id]) }}"
                                       class="btn btn-ghost btn-sm" title="View">👁</a>
                                    <a href="{{ route('inventory.edit', ['inventory' => $item->id]) }}"
                                       class="btn btn-ghost btn-sm" title="Edit">✎</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                <span>Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}</span>
                {{ $items->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-glyph">[ ]</div>
                <div class="empty-title">No results</div>
                <div class="empty-sub">No low stock items match your filters.</div>
            </div>
        @endif

    @endif

</x-layout>
