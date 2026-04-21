<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = ['name', 'category', 'status', 'quantity', 'attributes'];

    protected $casts = [
        'attributes' => 'array',   // Auto-cast JSON ↔ PHP array
    ];
}
