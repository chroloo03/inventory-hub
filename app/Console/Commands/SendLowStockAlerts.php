<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendLowStockAlerts extends Command
{
    protected $signature = 'inventory:send-low-stock-alerts
                            {--force : Send alert for ALL low stock items, ignoring already-notified state}
                            {--manual : Mark this as a manually triggered alert in the email}';

    protected $description = 'Send low stock email alerts to all admin users for items that have not yet been notified.';

    public function handle(): int
    {
        $isManual = $this->option('manual');
        $isForce  = $this->option('force');

        // Get items that need alerting
        $query = $isForce
            ? InventoryItem::lowStock()
            : InventoryItem::needsLowStockAlert();

        $items = $query->orderBy('quantity')->get();

        if ($items->isEmpty()) {
            $this->info('No low stock items require alerting. All good.');
            return self::SUCCESS;
        }

        $this->info("Found {$items->count()} item(s) requiring alert.");

        // Get all admin users
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            $this->error('No admin users found to send alerts to.');
            return self::FAILURE;
        }

        // Send email to each admin
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new LowStockAlert($items, $isManual));
            $this->line("  ✓ Alert sent to {$admin->email}");
        }

        // Stamp notified_at so we don't re-alert for same items tomorrow
        // unless stock is restored and drops again
        if (!$isForce) {
            InventoryItem::needsLowStockAlert()
                ->update(['low_stock_notified_at' => now()]);
        }

        $this->info("Done. {$items->count()} item(s) flagged as notified.");

        return self::SUCCESS;
    }
}
