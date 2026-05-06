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
        'attributes',
    ];

    protected $casts = [
        'attributes'          => 'array',
        'quantity'            => 'integer',
        'low_stock_threshold' => 'integer',
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
     * This is the single authoritative method for changing stock levels.
     * Nothing else should write to the quantity column directly after item creation.
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

        // Update the denormalized quantity cache
        $this->update(['quantity' => $after]);

        return $transaction;
    }
}
