<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Exports\TransactionsExport;
use App\Exports\InventorySnapshotExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    private function resolveDateRange(Request $request): array
    {
        $preset = $request->input('range', '30d');

        if ($preset === 'custom') {
            $from = Carbon::parse($request->input('from', now()->subDays(30)->toDateString()))->startOfDay();
            $to   = Carbon::parse($request->input('to',   now()->toDateString()))->endOfDay();
        } else {
            $to   = Carbon::now()->endOfDay();
            $from = match($preset) {
                'today' => Carbon::today()->startOfDay(),
                '7d'    => Carbon::now()->subDays(6)->startOfDay(),
                '30d'   => Carbon::now()->subDays(29)->startOfDay(),
                '90d'   => Carbon::now()->subDays(89)->startOfDay(),
                'year'  => Carbon::now()->startOfYear(),
                default => Carbon::now()->subDays(29)->startOfDay(),
            };
        }

        return [$from, $to, $preset];
    }

    public function index(Request $request): View
    {
        [$from, $to, $preset] = $this->resolveDateRange($request);

        $totalItems      = InventoryItem::count();
        $totalStock      = InventoryItem::sum('quantity');
        $lowStockCount   = InventoryItem::lowStock()->count();
        $totalCategories = InventoryItem::distinct('category')->count('category');

        $txQuery        = InventoryTransaction::whereBetween('created_at', [$from, $to]);
        $stockInPeriod  = (clone $txQuery)->where('type', 'in')->sum('quantity');
        $stockOutPeriod = (clone $txQuery)->where('type', 'out')->sum('quantity');
        $txCountPeriod  = (clone $txQuery)->count();

        $dailyMovement = InventoryTransaction::selectRaw("
                DATE(created_at) as date,
                SUM(CASE WHEN type = 'in'  THEN quantity ELSE 0 END) as total_in,
                SUM(CASE WHEN type = 'out' THEN quantity ELSE 0 END) as total_out
            ")
            ->whereBetween('created_at', [$from, $to])
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartIn     = [];
        $chartOut    = [];

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $dateKey       = $d->toDateString();
            $chartLabels[] = $d->format('M d');
            $row           = $dailyMovement->get($dateKey);
            $chartIn[]     = $row ? (int) $row->total_in  : 0;
            $chartOut[]    = $row ? (int) $row->total_out : 0;
        }

        $categoryStats = InventoryItem::selectRaw('category, COUNT(*) as item_count, SUM(quantity) as total_qty')
            ->groupBy('category')
            ->orderByDesc('total_qty')
            ->get();

        $statusStats = InventoryItem::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $topItems = InventoryTransaction::selectRaw('inventory_item_id, COUNT(*) as tx_count, SUM(quantity) as total_moved')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('inventory_item_id')
            ->orderByDesc('tx_count')
            ->limit(10)
            ->with('item:id,name,category,quantity')
            ->get();

        $recentTransactions = InventoryTransaction::with(['item:id,name,category', 'user:id,name'])
            ->latest()
            ->limit(10)
            ->get();

        $lowStockItems = InventoryItem::lowStock()
            ->orderByRaw('quantity - low_stock_threshold ASC')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'from', 'to', 'preset',
            'totalItems', 'totalStock', 'lowStockCount', 'totalCategories',
            'stockInPeriod', 'stockOutPeriod', 'txCountPeriod',
            'chartLabels', 'chartIn', 'chartOut',
            'categoryStats', 'statusStats',
            'topItems', 'recentTransactions', 'lowStockItems'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        return (new TransactionsExport($from, $to))->download();
    }

    public function exportSnapshot(): StreamedResponse
    {
        return (new InventorySnapshotExport())->download();
    }
}
