<?php

namespace App\Http\Controllers\Main;

use App\Console\Commands\SendLowStockAlerts;
use App\Http\Controllers\Controller;
use App\Mail\LowStockAlert;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class AlertController extends Controller
{
    /**
     * Manually trigger the low stock alert email.
     * Sends to all admin users regardless of notified_at state.
     */
    public function sendLowStockAlert(): RedirectResponse
    {
        $items = InventoryItem::lowStock()->orderBy('quantity')->get();

        if ($items->isEmpty()) {
            return back()->with('success', 'No low stock items found — no alert sent.');
        }

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new LowStockAlert($items, isManual: true));
        }

        // Stamp all currently low stock items as notified
        InventoryItem::lowStock()->update(['low_stock_notified_at' => now()]);

        $count = $items->count();
        return back()->with('success',
            "Low stock alert sent to {$admins->count()} admin(s) for {$count} item(s)."
        );
    }
}
