<x-layout title="{{ isset($item) ? 'Edit Item' : 'Add New Item' }}">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">// inventory / {{ isset($item) ? 'edit' : 'create' }}</div>
            <h1 class="page-title">{{ isset($item) ? 'Edit Item' : 'Add New Item' }}</h1>
            @if (isset($item))
                <div class="page-subtitle">Editing #{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }} — {{ $item->name }}
                </div>
            @endif
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">← Back</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            ✕ &nbsp;Please fix the following errors:<br>
            @foreach ($errors->all() as $error)
                &nbsp;&nbsp;· {{ $error }}<br>
            @endforeach
        </div>
    @endif

    @if (isset($item))
        <form method="POST" action="{{ route('inventory.update', ['inventory' => $item->id]) }}">
            @csrf
            @method('PUT')
        @else
            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf
    @endif

    <!-- Core Fields -->
    <div class="form-card">
        <div class="form-card-title">// Core Information</div>
        <div class="form-grid">

            <div class="form-group full-width">
                <label for="name">Item Name</label>
                <input type="text" id="name" name="name"
                    class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                    value="{{ old('name', $item->name ?? '') }}" placeholder='e.g. Dell UltraSharp 27" Monitor'
                    required autocomplete="off" />
                @error('name')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category"
                    class="form-control {{ $errors->has('category') ? 'is-invalid' : '' }}"
                    value="{{ old('category', $item->category ?? '') }}" placeholder="e.g. monitor, laptop, book"
                    list="category-suggestions" required autocomplete="off" />
                <datalist id="category-suggestions">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
                @error('category')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    @php $currentStatus = old('status', $item->status ?? 'available'); @endphp
                    <option value="available" {{ $currentStatus === 'available' ? 'selected' : '' }}>Available
                    </option>
                    <option value="checked_out" {{ $currentStatus === 'checked_out' ? 'selected' : '' }}>Checked Out
                    </option>
                    <option value="maintenance" {{ $currentStatus === 'maintenance' ? 'selected' : '' }}>Maintenance
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="location">Location <span class="text-muted">(optional)</span></label>
                <input type="text" id="location" name="location" class="form-control"
                    value="{{ old('location', $item->attributes['location'] ?? '') }}"
                    placeholder="e.g. Room 204, Shelf B" autocomplete="off" />
            </div>

        </div>
    </div>

    <!-- Stock Fields -->
    <div class="form-card">
        <div class="form-card-title">// Stock Management</div>
        <div class="form-grid">

            <div class="form-group">
                <label for="quantity">Quantity in Stock</label>
                <input type="number" id="quantity" name="quantity"
                    class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                    value="{{ old('quantity', $item->quantity ?? 1) }}" min="0" max="99999" required />
                @error('quantity')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="low_stock_threshold">Low Stock Threshold</label>
                <input type="number" id="low_stock_threshold" name="low_stock_threshold"
                    class="form-control {{ $errors->has('low_stock_threshold') ? 'is-invalid' : '' }}"
                    value="{{ old('low_stock_threshold', $item->low_stock_threshold ?? 5) }}" min="0"
                    max="99999" required />
                <span class="form-hint">Alert triggers when quantity ≤ this number.</span>
                @error('low_stock_threshold')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

        </div>
    </div>

    <!-- Custom Attributes -->
    <div class="form-card">
        <div class="form-card-title">// Custom Attributes</div>
        <p class="form-hint mb-3">
            Add category-specific attributes (e.g. brand, ram_gb, author, refresh_rate_hz).
        </p>

        <div class="attr-builder" id="attr-builder">
            @php
                $reserved = ['status', 'location'];
                $existingAttrs = [];
                if (isset($item) && is_array($item->attributes)) {
                    $existingAttrs = array_filter(
                        $item->attributes,
                        fn($k) => !in_array($k, $reserved),
                        ARRAY_FILTER_USE_KEY,
                    );
                }
                $oldKeys = old('attr_keys', array_keys($existingAttrs));
                $oldVals = old('attr_values', array_values($existingAttrs));
            @endphp

            @foreach ($oldKeys as $i => $key)
                <div class="attr-row">
                    <input type="text" name="attr_keys[]" class="form-control" placeholder="Key (e.g. brand)"
                        value="{{ $key }}" autocomplete="off" />
                    <input type="text" name="attr_values[]" class="form-control" placeholder="Value (e.g. Dell)"
                        value="{{ is_array($oldVals[$i] ?? '') ? implode(', ', $oldVals[$i]) : $oldVals[$i] ?? '' }}"
                        autocomplete="off" />
                    <button type="button" class="attr-remove" onclick="removeAttr(this)" title="Remove">×</button>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn-add-attr" onclick="addAttr()">+ Add Attribute</button>
    </div>

    <div style="display:flex; gap:12px; justify-content:flex-end;">
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            {{ isset($item) ? 'Save Changes' : 'Create Item' }}
        </button>
    </div>

    </form>
</x-layout>

<script>
    function addAttr() {
        const builder = document.getElementById('attr-builder');
        const row = document.createElement('div');
        row.className = 'attr-row';
        row.innerHTML = `
            <input type="text" name="attr_keys[]"   class="form-control" placeholder="Key (e.g. brand)"  autocomplete="off" />
            <input type="text" name="attr_values[]" class="form-control" placeholder="Value (e.g. Dell)" autocomplete="off" />
            <button type="button" class="attr-remove" onclick="removeAttr(this)" title="Remove">×</button>
        `;
        builder.appendChild(row);
        row.querySelector('input').focus();
    }

    function removeAttr(btn) {
        btn.closest('.attr-row').remove();
    }
</script>
