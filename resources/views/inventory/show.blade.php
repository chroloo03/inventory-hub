<x-layout title="{{ $item->name }}">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// inventory / #{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</div>
            <h1 class="page-title">{{ $item->name }}</h1>
            <div class="page-subtitle">{{ ucfirst($item->category) }}</div>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-secondary">✎ Edit</a>
            <button class="btn btn-danger"
                onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ route('inventory.destroy', $item->id) }}')">✕
                Delete</button>
        </div>
    </div>

    <div class="detail-grid">

        <!-- Core Info Card -->
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
                <span class="detail-val"
                    style="color:var(--cyan-dim); text-transform:uppercase; font-size:11px; letter-spacing:0.08em;">
                    {{ $item->category }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-key">Status</span>
                <span class="detail-val">
                    @php $status = $item->attributes['status'] ?? 'available'; @endphp
                    <span class="badge badge-{{ $status }}">{{ str_replace('_', ' ', $status) }}</span>
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
                <span class="detail-val">{{ $item->updated_at->format('M d, Y') }}</span>
            </div>
        </div>

        <!-- Custom Attributes Card -->
        <div class="detail-card">
            <div class="detail-card-title">// Custom Attributes</div>

            @php
                $reserved = ['status', 'location'];
                $customAttrs = array_filter(
                    $item->attributes ?? [],
                    fn($k) => !in_array($k, $reserved),
                    ARRAY_FILTER_USE_KEY,
                );
            @endphp

            @if (count($customAttrs))
                @foreach ($customAttrs as $key => $value)
                    <div class="detail-row">
                        <span class="detail-key">{{ str_replace('_', ' ', $key) }}</span>
                        <span class="detail-val">
                            {{-- Check if the value is an array or object to prevent TypeErrors --}}
                            @if (is_array($value))
                                {{ implode(', ', $value) }}
                            @elseif(is_object($value))
                                {{ json_encode($value) }}
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                @endforeach
            @else
                <div
                    style="padding: 24px 0; text-align:center; font-family:var(--mono); font-size:12px; color:var(--text-dim);">
                    No custom attributes defined.
                </div>
            @endif
        </div>

    </div>

    <a href="{{ route('inventory.index') }}" class="btn btn-ghost">← Back to All Items</a>

</x-layout>
