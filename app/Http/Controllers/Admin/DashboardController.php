<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyReset;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // ────────────────────────────────────────────────
    // Helper: Rentang waktu aktif bulan berjalan
    // (dari reset terakhir atau dari awal bulan)
    // ────────────────────────────────────────────────
    private function getActivePeriod(): array
    {
        $now        = Carbon::now();
        $month      = $now->month;
        $year       = $now->year;
        $lastReset  = MonthlyReset::latestForMonth($month, $year);

        // Jika sudah pernah di-reset bulan ini, mulai dari waktu reset
        $startFrom = $lastReset
            ? $lastReset->reset_at
            : Carbon::create($year, $month, 1, 0, 0, 0);

        return [
            'start'      => $startFrom,
            'end'        => $now,
            'month'      => $month,
            'year'       => $year,
            'last_reset' => $lastReset,
        ];
    }

    // ────────────────────────────────────────────────
    // Dashboard Index
    // ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $days = $request->input('days', 7);
        if (! in_array($days, [3, 7, 30])) {
            $days = 7;
        }

        $period = $this->getActivePeriod();

        // Statistik bulan berjalan (sejak reset terakhir atau awal bulan)
        $monthlyRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->sum('total_amount');

        $monthlyOrders = Order::whereBetween('created_at', [$period['start'], $period['end']])
            ->count();

        $totalProducts    = Product::count();
        $lowStockProducts = Product::where('stock', '<=', 10)->get();
        $recentOrders     = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get();

        // Chart: Pendapatan harian N hari terakhir
        $dates = collect(range($days - 1, 0))->map(function ($d) {
            return now()->subDays($d)->format('Y-m-d');
        });

        $chartData = $dates->map(function ($date) {
            return Order::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total_amount');
        })->values();

        return view('admin.dashboard', compact(
            'monthlyRevenue',
            'monthlyOrders',
            'totalProducts',
            'lowStockProducts',
            'recentOrders',
            'dates',
            'chartData',
            'days',
            'period',
        ));
    }

    // ────────────────────────────────────────────────
    // Reset Statistik Bulan Ini
    // ────────────────────────────────────────────────
    public function resetMonthlyStats(Request $request)
    {
        $now    = Carbon::now();
        $month  = $now->month;
        $year   = $now->year;
        $period = $this->getActivePeriod();

        // Ambil snapshot sebelum reset
        $revenueSnapshot = Order::where('status', 'completed')
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->sum('total_amount');

        $ordersSnapshot = Order::whereBetween('created_at', [$period['start'], $period['end']])
            ->count();

        MonthlyReset::create([
            'reset_by'         => auth()->id(),
            'month'            => $month,
            'year'             => $year,
            'revenue_snapshot' => $revenueSnapshot,
            'orders_snapshot'  => $ordersSnapshot,
            'reset_at'         => $now,
            'note'             => $request->input('note'),
        ]);

        $monthName = $now->locale('id')->isoFormat('MMMM Y');

        return redirect()->route('admin.dashboard')
            ->with('success', "Statistik bulan {$monthName} berhasil di-reset. Snapshot pendapatan Rp " . number_format($revenueSnapshot, 0, ',', '.') . " disimpan.");
    }

    // ────────────────────────────────────────────────
    // Hapus Pesanan Pending
    // ────────────────────────────────────────────────
    public function deletePendingOrders()
    {
        try {
            DB::beginTransaction();

            $pendingOrders = Order::where('status', 'pending')->with('items.product')->get();
            $count = $pendingOrders->count();

            foreach ($pendingOrders as $order) {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->stock += $item->quantity;
                        if ($product->stock > 10) {
                            $product->status = 'ready';
                        } elseif ($product->stock > 0) {
                            $product->status = 'limited';
                        }
                        $product->save();
                    }
                }
                $order->delete();
            }

            DB::commit();

            return redirect()->route('admin.dashboard')
                ->with('success', "$count pesanan pending berhasil dihapus dan stok dikembalikan.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('admin.dashboard')
                ->with('error', 'Gagal menghapus pesanan pending: ' . $e->getMessage());
        }
    }

    // ────────────────────────────────────────────────
    // Export CSV — hanya bulan berjalan
    // ────────────────────────────────────────────────
    public function exportCsv()
    {
        $period   = $this->getActivePeriod();
        $monthName = Carbon::create($period['year'], $period['month'], 1)->locale('id')->isoFormat('MMMM_YYYY');
        $fileName = 'laporan_bulanan_art_coffee_' . strtolower($monthName) . '.csv';

        $orders = Order::with(['user', 'payment'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = ['No. Order', 'Tanggal', 'Pelanggan', 'Tipe', 'Subtotal (Rp)', 'Pajak (Rp)', 'Total (Rp)', 'Metode Pembayaran'];

        $callback = function () use ($orders, $columns) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM untuk Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $columns);

            foreach ($orders as $order) {
                $subtotal    = $order->total_amount - $order->tax;
                $paymentType = $order->payment
                    ? ($order->payment->payment_method === 'cash' ? 'Cash' : $order->payment->payment_type)
                    : 'Midtrans';

                fputcsv($file, [
                    $order->order_number,
                    Carbon::parse($order->created_at)->format('Y-m-d H:i') . ' WIB',
                    $order->user ? $order->user->name : 'POS Kasir',
                    $order->type,
                    $subtotal,
                    $order->tax,
                    $order->total_amount,
                    strtoupper(str_replace('_', ' ', $paymentType)),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ────────────────────────────────────────────────
    // Export PDF — hanya bulan berjalan
    // ────────────────────────────────────────────────
    public function exportPdf()
    {
        $period = $this->getActivePeriod();

        $orders = Order::with(['user', 'payment'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$period['start'], $period['end']])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalRevenue  = $orders->sum('total_amount');
        $totalOrders   = $orders->count();
        $averageOrder  = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $monthLabel    = Carbon::create($period['year'], $period['month'], 1)
            ->locale('id')->isoFormat('MMMM Y');

        return view('admin.reports.print_report', compact(
            'orders',
            'totalRevenue',
            'totalOrders',
            'averageOrder',
            'period',
            'monthLabel',
        ));
    }
}
