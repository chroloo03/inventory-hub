<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'type',
        'quantity',
        'reason',
        'notes',
        'quantity_before',
        'quantity_after',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'quantity_before' => 'integer',
        'quantity_after'  => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isStockIn(): bool
    {
        return $this->type === 'in';
    }

    public function isStockOut(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Net change as a signed integer: +5 or -5.
     */
    public function netChange(): int
    {
        return $this->type === 'in' ? $this->quantity : -$this->quantity;
    }
}
