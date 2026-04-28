<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = InventoryItem::query();

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
            ->orderByRaw("FIELD(status, 'available', 'maintenance', 'checked_out')")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $categories    = InventoryItem::distinct()->pluck('category')->sort()->values();
        $lowStockCount = InventoryItem::lowStock()->count();

        return view('inventory.index', compact('items', 'categories', 'lowStockCount'));
    }

    public function create(): View
    {
        $categories = InventoryItem::distinct()->pluck('category')->sort()->values();
        return view('inventory.form', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'category'           => 'required|string|max:100',
            'status'             => 'required|in:available,checked_out,maintenance',
            'quantity'           => 'required|integer|min:0|max:99999',
            'low_stock_threshold'=> 'required|integer|min:0|max:99999',
        ]);

        InventoryItem::create([
            'name'               => $request->input('name'),
            'category'           => strtolower(trim($request->input('category'))),
            'status'             => $request->input('status'),
            'quantity'           => $request->input('quantity'),
            'low_stock_threshold'=> $request->input('low_stock_threshold'),
            'attributes'         => $this->buildAttributes($request),
        ]);

        return redirect()->route('inventory.index')
            ->with('success', "Item \"{$request->input('name')}\" created successfully.");
    }

    public function show(InventoryItem $inventory): View
    {
        return view('inventory.show', ['item' => $inventory]);
    }

    public function edit(InventoryItem $inventory): View
    {
        $categories = InventoryItem::distinct()->pluck('category')->sort()->values();
        return view('inventory.form', ['item' => $inventory, 'categories' => $categories]);
    }

    public function update(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'category'           => 'required|string|max:100',
            'status'             => 'required|in:available,checked_out,maintenance',
            'quantity'           => 'required|integer|min:0|max:99999',
            'low_stock_threshold'=> 'required|integer|min:0|max:99999',
        ]);

        $inventory->update([
            'name'               => $request->input('name'),
            'category'           => strtolower(trim($request->input('category'))),
            'status'             => $request->input('status'),
            'quantity'           => $request->input('quantity'),
            'low_stock_threshold'=> $request->input('low_stock_threshold'),
            'attributes'         => $this->buildAttributes($request),
        ]);

        return redirect()->route('inventory.show', ['inventory' => $inventory->id])
            ->with('success', "Item \"{$inventory->name}\" updated successfully.");
    }

    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        $name = $inventory->name;
        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', "Item \"{$name}\" deleted.");
    }

    private function buildAttributes(Request $request): array
    {
        $attributes = [
            'status'   => $request->input('status', 'available'),
            'location' => $request->input('location', ''),
        ];

        $keys   = $request->input('attr_keys', []);
        $values = $request->input('attr_values', []);

        foreach ($keys as $i => $key) {
            $key = trim($key);
            if ($key !== '') {
                $attributes[$key] = (string) trim($values[$i] ?? '');
            }
        }

        return $attributes;
    }
}
