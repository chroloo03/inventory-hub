<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Show the full transaction ledger for one item.
     */
    public function index(Request $request, InventoryItem $inventory): View
    {
        $transactions = $inventory
            ->transactions()
            ->with('user')
            ->latest()
            ->paginate(20);

        // Summary stats for the ledger header
        $totalIn  = $inventory->transactions()->where('type', 'in')->sum('quantity');
        $totalOut = $inventory->transactions()->where('type', 'out')->sum('quantity');

        return view('inventory.transactions', compact(
            'inventory',
            'transactions',
            'totalIn',
            'totalOut'
        ));
    }

    /**
     * Show the stock in or stock out form.
     * Route: GET /inventory/{inventory}/transactions/create?type=in|out
     */
    public function create(Request $request, InventoryItem $inventory): View
    {
        $type = $request->query('type', 'out');

        abort_if(!in_array($type, ['in', 'out']), 400);

        return view('inventory.transaction-form', compact('inventory', 'type'));
    }

    /**
     * Process and store the transaction.
     * Route: POST /inventory/{inventory}/transactions
     */
    public function store(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'type'     => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason'   => ['required', 'string', 'max:255'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $inventory->applyTransaction(
                type:     $validated['type'],
                quantity: $validated['quantity'],
                reason:   $validated['reason'],
                notes:    $validated['notes'] ?? null,
                userId:   $request->user()->id,
            );
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => $e->getMessage()]);
        }

        $direction = $validated['type'] === 'in' ? 'stocked in' : 'stocked out';

        return redirect()
            ->route('inventory.show', ['inventory' => $inventory->id])
            ->with('success', "Successfully {$direction} {$validated['quantity']} unit(s) of \"{$inventory->name}\".");
    }
}
