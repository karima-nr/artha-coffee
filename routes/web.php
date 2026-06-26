<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Guest Order (no auth required) - pelanggan pesan langsung dari meja
Route::post('/checkout/online', [HomeController::class, 'processCheckout'])->name('checkout.online');
Route::post('/checkout/qris', [HomeController::class, 'processQrisCheckout'])->name('checkout.qris');

// Public Order Tracking (no auth) - pelanggan cek status pesanan
Route::get('/track/{order_number}', [HomeController::class, 'trackOrder'])->name('orders.track');

// Midtrans Webhook Notification
Route::post('/payment/callback', [PaymentCallbackController::class, 'handleCallback'])->name('payment.callback');

// Customer Dashboard & Orders (still available for staff)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::post('/reviews', [CustomerController::class, 'storeReview'])->name('reviews.store');

    // Digital Invoice & Real-time Status Polling
    Route::get('/orders/{order_number}/invoice', [CustomerController::class, 'showInvoice'])->name('orders.invoice');
    Route::get('/orders/{order_number}/status', [CustomerController::class, 'checkStatus'])->name('orders.status');
    Route::post('/orders/{order_number}/simulate-success', [KasirController::class, 'simulateSuccess'])->name('orders.simulate_success');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::delete('/orders/pending', [DashboardController::class, 'deletePendingOrders'])->name('orders.delete_pending');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);

    // Admin Report Exports
    Route::get('/reports/export/csv', [DashboardController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [DashboardController::class, 'exportPdf'])->name('reports.export.pdf');

    // Monthly Stats Reset
    Route::post('/stats/reset', [DashboardController::class, 'resetMonthlyStats'])->name('stats.reset');
});

// Kasir Routes (Accessible by kasir and admin)
Route::middleware(['auth', 'verified', 'role:kasir,admin'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('index');
    Route::post('/checkout', [KasirController::class, 'checkout'])->name('checkout');

    // Order Management — route statis harus di atas route dengan parameter dinamis
    Route::get('/orders/active', [KasirController::class, 'activeOrders'])->name('orders.active');
    Route::patch('/orders/{id}/status', [KasirController::class, 'updateOrderStatus'])->name('orders.update_status');

    // Print Thermal Receipt
    Route::get('/orders/{order_number}/receipt', [KasirController::class, 'printReceipt'])->name('orders.receipt');
});
