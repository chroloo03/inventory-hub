<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LowStockController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryItem::lowStock();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $items = $query
            ->orderByRaw('quantity - low_stock_threshold ASC') // most critical first
            ->paginate(20)
            ->withQueryString();

        $categories    = InventoryItem::distinct()->pluck('category')->sort()->values();
        $lowStockCount = InventoryItem::lowStock()->count();

        return view('inventory.low-stock', compact('items', 'categories', 'lowStockCount'));
    }
}
