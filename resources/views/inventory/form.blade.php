<x-layout title="{{ isset($item) ? 'Edit Item' : 'Add New Item' }}">

    <!-- Page Header -->
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

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-error">
            ✕ &nbsp;Please fix the following errors:<br>
            @foreach ($errors->all() as $error)
                &nbsp;&nbsp;· {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <form method="POST"
        action="@if (isset($item)) {{ route('inventory.update', ['inventory' => $item->id]) }}@else{{ route('inventory.store') }} @endif">
        @csrf
        @if (isset($item))
            @method('PUT')
        @endif

        <!-- Core Fields -->
        <div class="form-card">
            <div class="form-card-title">// Core Information</div>
            <div class="form-grid">

                <!-- Name -->
                <div class="form-group full-width">
                    <label for="name">Item Name</label>
                    <input type="text" id="name" name="name"
                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        value="{{ old('name', $item->name ?? '') }}"
                        placeholder="e.g. Dell UltraSharp 27&quot; Monitor" required autocomplete="off" />
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Category -->
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category"
                        class="form-control {{ $errors->has('category') ? 'is-invalid' : '' }}"
                        value="{{ old('category', $item->category ?? '') }}"
                        placeholder="e.g. monitor, laptop, book, vehicle" list="category-suggestions" required
                        autocomplete="off" />
                    <datalist id="category-suggestions">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                    @error('category')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                    <span class="form-hint">Lowercase, singular — existing categories shown as suggestions.</span>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        @php $currentStatus = old('status', $item->attributes['status'] ?? 'available'); @endphp
                        <option value="available" {{ $currentStatus === 'available' ? 'selected' : '' }}>Available
                        </option>
                        <option value="checked_out" {{ $currentStatus === 'checked_out' ? 'selected' : '' }}>Checked
                            Out</option>
                        <option value="maintenance" {{ $currentStatus === 'maintenance' ? 'selected' : '' }}>
                            Maintenance</option>
                    </select>
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label for="location">Location <span class="text-dim">(optional)</span></label>
                    <input type="text" id="location" name="location" class="form-control"
                        value="{{ old('location', $item->attributes['location'] ?? '') }}"
                        placeholder="e.g. Room 204, Shelf B" autocomplete="off" />
                </div>

            </div>
        </div>

        <!-- Dynamic Attributes -->
        <div class="form-card">
            <div class="form-card-title">// Custom Attributes</div>
            <p class="form-hint mb-3">
                Add any additional attributes specific to this item's category (e.g. brand, ram_gb, refresh_rate_hz,
                author).
            </p>

            <div class="attr-builder" id="attr-builder">
                @php
                    // Pull existing attrs, exclude reserved keys handled above
                    $reserved = ['status', 'location'];
                    $existingAttrs = [];
                    if (isset($item) && is_array($item->attributes)) {
                        $existingAttrs = array_filter(
                            $item->attributes,
                            fn($k) => !in_array($k, $reserved),
                            ARRAY_FILTER_USE_KEY,
                        );
                    }
                    // Merge with old() input if present
                    $oldKeys = old('attr_keys', array_keys($existingAttrs));
                    $oldVals = old('attr_values', array_values($existingAttrs));
                @endphp

                @if (count($oldKeys))
                    @foreach ($oldKeys as $i => $key)
                        @php
                            // Safely convert arrays/objects to strings for the input field
                            $val = $oldVals[$i] ?? '';
                            if (is_array($val)) {
                                $val = implode(', ', $val);
                            } elseif (is_object($val)) {
                                $val = json_encode($val);
                            }
                        @endphp
                        <div class="attr-row">
                            <input type="text" name="attr_keys[]" class="form-control" placeholder="Key (e.g. brand)"
                                value="{{ is_array($key) ? json_encode($key) : $key }}" autocomplete="off" />
                            <input type="text" name="attr_values[]" class="form-control"
                                placeholder="Value (e.g. Dell)" value="{{ $val }}" autocomplete="off" />
                            <button type="button" class="attr-remove" onclick="removeAttr(this)"
                                title="Remove">×</button>
                        </div>
                    @endforeach
                @endif
            </div>

            <button type="button" class="btn-add-attr" onclick="addAttr()">
                + Add Attribute
            </button>
        </div>

        <!-- Form Actions -->
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
            <input type="text"  name="attr_keys[]"   class="form-control" placeholder="Key (e.g. brand)"  autocomplete="off" />
            <input type="text"  name="attr_values[]" class="form-control" placeholder="Value (e.g. Dell)" autocomplete="off" />
            <button type="button" class="attr-remove" onclick="removeAttr(this)" title="Remove">×</button>
        `;
        builder.appendChild(row);
        row.querySelector('input').focus();
    }

    function removeAttr(btn) {
        btn.closest('.attr-row').remove();
    }
</script>
