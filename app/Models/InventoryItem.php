<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'status',
        'quantity',
        'low_stock_threshold',
        'low_stock_notified_at',
        'attributes',
    ];

    protected $casts = [
        'attributes'            => 'array',
        'quantity'              => 'integer',
        'low_stock_threshold'   => 'integer',
        'low_stock_notified_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    /**
     * Items that are low on stock AND haven't been notified yet.
     * Once notified, low_stock_notified_at is set.
     * It's reset to NULL when stock is restored above threshold,
     * so the next drop triggers a fresh alert.
     */
    public function scopeNeedsLowStockAlert(Builder $query): Builder
    {
        return $query
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->whereNull('low_stock_notified_at');
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    public function stockDeficit(): int
    {
        return max(0, $this->low_stock_threshold - $this->quantity);
    }

    /**
     * Apply a stock transaction and update the cached quantity column.
     *
     * @throws \InvalidArgumentException if a stock-out would make quantity negative
     */
    public function applyTransaction(
        string  $type,
        int     $quantity,
        string  $reason,
        ?string $notes,
        int     $userId
    ): InventoryTransaction {
        $before = $this->quantity;

        if ($type === 'out' && $quantity > $before) {
            throw new \InvalidArgumentException(
                "Cannot remove {$quantity} unit(s). Only {$before} in stock."
            );
        }

        $after = $type === 'in'
            ? $before + $quantity
            : $before - $quantity;

        $transaction = $this->transactions()->create([
            'user_id'         => $userId,
            'type'            => $type,
            'quantity'        => $quantity,
            'reason'          => $reason,
            'notes'           => $notes,
            'quantity_before' => $before,
            'quantity_after'  => $after,
        ]);

        $this->update(['quantity' => $after]);

        // If restocked above threshold, reset notification flag
        // so the next drop triggers a fresh alert
        if ($type === 'in' && $after > $this->low_stock_threshold) {
            $this->update(['low_stock_notified_at' => null]);
        }

        return $transaction;
    }
}
