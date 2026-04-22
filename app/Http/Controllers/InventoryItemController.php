<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    /**
     * List all inventory items with search, filter, and pagination.
     */
    public function index(Request $request): View
    {
        $query = InventoryItem::query();

        // Search by name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by category
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Filter by status (stored inside JSON attributes column)
        if ($status = $request->input('status')) {
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.status')) = ?",
                [$status]
            );
        }

        // Sort available items first, then paginate
        $items = $query
            ->orderByRaw("FIELD(JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.status')), 'available', 'maintenance', 'checked_out')")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Distinct categories for the filter dropdown
        $categories = InventoryItem::distinct()->pluck('category')->sort()->values();

        return view('inventory.index', compact('items', 'categories'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        $categories = InventoryItem::distinct()->pluck('category')->sort()->values();
        return view('inventory.form', compact('categories'));
    }

    /**
     * Store a new inventory item.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status'   => 'required|in:available,checked_out,maintenance',
        ]);

        $attributes = $this->buildAttributes($request);

        InventoryItem::create([
            'name'       => $request->input('name'),
            'category'   => strtolower(trim($request->input('category'))),
            'attributes' => $attributes,
        ]);

        return redirect()->route('inventory.index')
            ->with('success', "Item \"{$request->input('name')}\" created successfully.");
    }

    /**
     * Show a single item.
     */
    public function show(InventoryItem $inventory): View
    {
        return view('inventory.show', ['item' => $inventory]);
    }

    /**
     * Show the edit form.
     */
    public function edit(InventoryItem $inventory): View
    {
        $categories = InventoryItem::distinct()->pluck('category')->sort()->values();
        return view('inventory.form', ['item' => $inventory, 'categories' => $categories]);
    }

    /**
     * Update an existing inventory item.
     */
    /**
     * Update an existing inventory item.
     */
    public function update(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'status'   => 'required|in:available,checked_out,maintenance',
        ]);

        $attributes = $this->buildAttributes($request);

        $inventory->update([
            'name'       => $request->input('name'),
            'category'   => strtolower(trim($request->input('category'))),
            'attributes' => $attributes,
        ]);

        return redirect()->route('inventory.show', $inventory)
            ->with('success', "Item \"{$inventory->name}\" updated successfully.");
    }

    /**
     * Delete an inventory item.
     */
    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        $name = $inventory->name;
        $inventory->delete();

        return redirect()->route('inventory.index')
            ->with('success', "Item \"{$name}\" deleted.");
    }

    /**
     * Build the attributes JSON array from the form input.
     * Always includes status and location, plus any dynamic key/value pairs.
     */
    private function buildAttributes(Request $request): array
    {
        $attributes = [
            'status'   => $request->input('status', 'available'),
            'location' => $request->input('location', ''),
        ];

        // Merge dynamic attribute key-value pairs
        $keys   = $request->input('attr_keys', []);
        $values = $request->input('attr_values', []);

        foreach ($keys as $i => $key) {
            $key = trim($key);
            if ($key !== '') {
                $attributes[$key] = trim($values[$i] ?? '');
            }
        }

        return $attributes;
    }
}
