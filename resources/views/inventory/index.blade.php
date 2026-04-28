<x-layout title="All Inventory Items">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// inventory</div>
            <h1 class="page-title">All Items</h1>
            <div class="page-subtitle">{{ $items->total() }} items in database</div>
        </div>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">+ Add Item</a>
    </div>

    <form method="GET" action="{{ route('inventory.index') }}" id="filter-form">
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

                @if(request('search') || request('category') || request('status'))
                    <a href="{{ route('inventory.index') }}" class="btn btn-ghost btn-sm">✕ Clear</a>
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
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td class="td-id">#{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="td-name">
                            <a href="{{ route('inventory.show', ['inventory' => $item->id]) }}">{{ $item->name }}</a>
                        </td>
                        <td class="td-category">{{ $item->category }}</td>
                        <td><span class="badge badge-{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span></td>
                        <td>
                            <span class="qty-cell {{ $item->isLowStock() ? 'qty-low' : '' }}">
                                {{ $item->quantity }}
                                @if($item->isLowStock())
                                    <span class="qty-warn-icon" title="Low stock">⚠</span>
                                @endif
                            </span>
                        </td>
                        <td class="text-mono" style="font-size:12px; color:var(--text-dim)">
                            {{ $item->attributes['location'] ?? '—' }}
                        </td>
                        <td>
                            <div class="td-actions">
                                <a href="{{ route('inventory.show', ['inventory' => $item->id]) }}" class="btn btn-ghost btn-sm" title="View">👁</a>
                                <a href="{{ route('inventory.edit', ['inventory' => $item->id]) }}" class="btn btn-ghost btn-sm" title="Edit">✎</a>
                                <button class="btn btn-ghost btn-sm" title="Delete" style="color:var(--red)"
                                    onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ route('inventory.destroy', ['inventory' => $item->id]) }}')">✕</button>
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
            <div class="empty-title">No items found</div>
            <div class="empty-sub">
                @if(request('search') || request('category') || request('status'))
                    No items match your current filters.
                @else
                    The inventory is empty. Add your first item to get started.
                @endif
            </div>
            @if(!request()->hasAny(['search','category','status']))
                <a href="{{ route('inventory.create') }}" class="btn btn-primary">+ Add First Item</a>
            @endif
        </div>
    @endif

</x-layout>
