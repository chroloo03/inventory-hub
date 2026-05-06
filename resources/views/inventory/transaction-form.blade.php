<x-layout title="{{ $type === 'in' ? 'Stock In' : 'Stock Out' }} — {{ $inventory->name }}">

    <div class="page-header">
        <div class="page-title-group">
            <div class="page-eyebrow">
                // inventory / #{{ str_pad($inventory->id, 4, '0', STR_PAD_LEFT) }} / {{ $type === 'in' ? 'stock in' : 'stock out' }}
            </div>
            <h1 class="page-title" style="color: {{ $type === 'in' ? 'var(--green)' : 'var(--red)' }}">
                {{ $type === 'in' ? '↑ Stock In' : '↓ Stock Out' }}
            </h1>
            <div class="page-subtitle">{{ $inventory->name }}</div>
        </div>
        <a href="{{ route('inventory.show', ['inventory' => $inventory->id]) }}" class="btn btn-secondary">← Back</a>
    </div>

    {{-- Current stock context card --}}
    <div class="form-card" style="border-color: {{ $type === 'in' ? 'var(--green-border)' : 'var(--red-border)' }}; margin-bottom:20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px;">
            <div>
                <div style="font-family:var(--mono); font-size:10px; letter-spacing:0.12em; text-transform:uppercase;
                            color:var(--text-dim); margin-bottom:4px;">Current Stock</div>
                <div style="font-size:32px; font-weight:800; color:{{ $inventory->isLowStock() ? 'var(--red)' : 'var(--text-hi)' }};
                            font-family:var(--mono); line-height:1;">
                    {{ $inventory->quantity }}
                    <span style="font-size:14px; font-weight:400; color:var(--text-dim);">units</span>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-family:var(--mono); font-size:10px; letter-spacing:0.12em; text-transform:uppercase;
                            color:var(--text-dim); margin-bottom:4px;">Low Stock At</div>
                <div style="font-size:20px; font-weight:700; color:var(--amber); font-family:var(--mono);">
                    ≤ {{ $inventory->low_stock_threshold }}
                </div>
            </div>
            <div>
                <span class="badge badge-{{ $inventory->status }}">{{ str_replace('_', ' ', $inventory->status) }}</span>
                @if($inventory->isLowStock())
                    <span class="badge badge-maintenance" style="margin-left:6px;">⚠ Low Stock</span>
                @endif
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            ✕ &nbsp;{{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.transactions.store', ['inventory' => $inventory->id]) }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}" />

        <div class="form-card">
            <div class="form-card-title" style="color: {{ $type === 'in' ? 'var(--green)' : 'var(--red)' }}">
                // {{ $type === 'in' ? 'Stock In Details' : 'Stock Out Details' }}
            </div>

            <div class="form-grid">

                {{-- Quantity --}}
                <div class="form-group">
                    <label for="quantity">
                        Quantity to {{ $type === 'in' ? 'Add' : 'Remove' }}
                    </label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}"
                        value="{{ old('quantity', 1) }}"
                        min="1"
                        @if($type === 'out') max="{{ $inventory->quantity }}" @endif
                        required
                        autofocus
                    />
                    @if($type === 'out')
                        <span class="form-hint">Max: {{ $inventory->quantity }} units available.</span>
                    @endif
                    @error('quantity')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Reason --}}
                <div class="form-group">
                    <label for="reason">Reason</label>
                    <input
                        type="text"
                        id="reason"
                        name="reason"
                        class="form-control {{ $errors->has('reason') ? 'is-invalid' : '' }}"
                        value="{{ old('reason') }}"
                        placeholder="{{ $type === 'in' ? 'e.g. Restocked from supplier' : 'e.g. Issued to IT dept' }}"
                        list="reason-suggestions"
                        required
                        autocomplete="off"
                    />
                    <datalist id="reason-suggestions">
                        @if($type === 'in')
                            <option value="Restocked from supplier">
                            <option value="Returned by staff">
                            <option value="Initial stock entry">
                            <option value="Adjustment — count correction">
                        @else
                            <option value="Issued to department">
                            <option value="Damaged / defective">
                            <option value="Lost or missing">
                            <option value="Used for maintenance">
                            <option value="Returned to supplier">
                        @endif
                    </datalist>
                    @error('reason')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Notes --}}
                <div class="form-group full-width">
                    <label for="notes">Notes <span class="text-muted">(optional)</span></label>
                    <textarea
                        id="notes"
                        name="notes"
                        class="form-control"
                        rows="3"
                        placeholder="Any additional context, reference numbers, or remarks..."
                    >{{ old('notes') }}</textarea>
                </div>

            </div>

            {{-- Live preview of what the new quantity will be --}}
            <div id="preview-bar" style="margin-top:20px; padding:14px 18px;
                 background:var(--surface-2); border:1px solid var(--border);
                 border-radius:var(--radius-sm); font-family:var(--mono); font-size:13px;">
                <span style="color:var(--text-dim);">New quantity after this transaction: </span>
                <strong id="preview-qty" style="color:var(--text-hi); font-size:16px;">
                    {{ $inventory->quantity }}
                </strong>
                <span style="color:var(--text-dim);"> units</span>
            </div>

        </div>

        <div style="display:flex; gap:12px; justify-content:flex-end;">
            <a href="{{ route('inventory.show', ['inventory' => $inventory->id]) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn" style="
                background: {{ $type === 'in' ? 'var(--green)' : 'var(--red)' }};
                color: #fff;
                border-color: {{ $type === 'in' ? 'var(--green)' : 'var(--red)' }};
            ">
                {{ $type === 'in' ? '↑ Confirm Stock In' : '↓ Confirm Stock Out' }}
            </button>
        </div>
    </form>

</x-layout>

<script>
    const qtyInput   = document.getElementById('quantity');
    const previewQty = document.getElementById('preview-qty');
    const current    = {{ $inventory->quantity }};
    const type       = '{{ $type }}';
    const threshold  = {{ $inventory->low_stock_threshold }};

    function updatePreview() {
        const val = parseInt(qtyInput.value) || 0;
        const newQty = type === 'in' ? current + val : current - val;
        previewQty.textContent = Math.max(0, newQty);
        previewQty.style.color = newQty <= threshold
            ? 'var(--red)'
            : newQty <= threshold * 1.5
                ? 'var(--amber)'
                : 'var(--green)';
    }

    qtyInput.addEventListener('input', updatePreview);
    updatePreview();
</script>
