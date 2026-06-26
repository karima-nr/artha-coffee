<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // Redirect Admin and Kasir to their respective dashboards
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'kasir') {
            return redirect()->route('kasir.index');
        }

        $orders = Order::with('items.product')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return view('dashboard', compact('orders'));
    }

    public function storeReview(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if review already exists
        if (Review::where('order_id', $request->order_id)->exists()) {
            return back()->with('error', 'Anda sudah memberikan review untuk pesanan ini.');
        }

        $order = Order::find($request->order_id);

        Review::create([
            'user_id' => auth()->id(), // Will be null for guests
            'customer_name' => $order->customer_name ?? 'Pelanggan Meja',
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Terima kasih atas review Anda!');
    }

    public function showInvoice($orderNumber)
    {
        $order = Order::with(['items.product', 'payment', 'user'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Security Check: Only restrict if order belongs to a registered customer and a different registered user is trying to view it.
        if (auth()->check() && auth()->user()->role === 'user' && $order->user_id !== null && $order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('orders.invoice', compact('order'));
    }

    public function checkStatus($orderNumber)
    {
        $order = Order::with('payment')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Security Check
        if (auth()->check() && auth()->user()->role === 'user' && $order->user_id !== null && $order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => $order->status,
            'payment_status' => $order->payment ? $order->payment->status : 'pending',
            'transaction_status' => $order->payment ? $order->payment->transaction_status : null,
        ]);
    }
}
