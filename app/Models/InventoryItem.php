<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * Scope: items where quantity is at or below their low_stock_threshold.
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    /**
     * Determine if this item is considered low stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    /**
     * How many units below the threshold (for sorting criticality).
     */
    public function stockDeficit(): int
    {
        return max(0, $this->low_stock_threshold - $this->quantity);
    }
}
