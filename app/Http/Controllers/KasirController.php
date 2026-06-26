<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KasirController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $products = Product::with('category')->get();

        return view('kasir.index', compact('categories', 'products'));
    }

    /**
     * Return active orders (pending + processing) as JSON for live dashboard panel.
     */
    public function activeOrders()
    {
        $orders = Order::with(['items.product', 'payment'])
            ->whereIn('status', ['pending', 'processing'])
            ->where('type', 'offline')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'order_number'   => $order->order_number,
                    'customer_name'  => $order->customer_name ?? 'Tamu',
                    'status'         => $order->status,
                    'total_amount'   => $order->total_amount,
                    'payment_method' => $order->payment?->payment_method ?? '-',
                    'created_at'     => $order->created_at->format('H:i'),
                    'items'          => $order->items->map(fn ($i) => [
                        'name'     => $i->product->name ?? 'Produk',
                        'quantity' => $i->quantity,
                    ]),
                ];
            });

        return response()->json($orders);
    }

    /**
     * Update an order's status from the kasir order management panel.
     * Automatically restores stock when an order is cancelled.
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::with('items.product')->findOrFail($id);

            if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->stock += $item->quantity;
                        $product->status = $product->stock > 10 ? 'available' : 'limited';
                        $product->save();
                    }
                }

                if ($order->payment) {
                    $order->payment->status = 'cancelled';
                    $order->payment->save();
                }
            }

            $order->status = $request->status;
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui.',
                'status'  => $order->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function checkout(Request $request, MidtransService $midtransService)
    {
        $request->validate([
            'customer_name'    => 'nullable|string',
            'payment_method'   => 'required|in:cash,qris,midtrans',
            'amount_paid'      => 'nullable|numeric',
            'items'            => 'required|array',
            'items.*.id'       => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $orderItemsData = [];
            $itemsListForMidtrans = [];

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->find($item['id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok tidak mencukupi untuk {$product->name}");
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $product->stock -= $item['quantity'];
                if ($product->stock == 0) {
                    $product->status = 'sold_out';
                } elseif ($product->stock <= 10) {
                    $product->status = 'limited';
                }
                $product->save();

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                    'subtotal'   => $itemSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $itemsListForMidtrans[] = [
                    'id'       => $product->id,
                    'price'    => $product->price,
                    'quantity' => $item['quantity'],
                    'name'     => $product->name,
                ];
            }

            $tax = $subtotal * 0.10;
            $totalAmount = $subtotal + $tax;
            $isCash = $request->payment_method === 'cash';

            if ($isCash && (empty($request->amount_paid) || $request->amount_paid < $totalAmount)) {
                throw new \Exception('Uang yang dibayarkan kurang dari total belanja.');
            }

            // All POS offline orders start as 'pending' — kasir marks them complete from the panel
            $orderNumber = 'INV-' . now()->format('Ymd') . '-OFF-' . strtoupper(Str::random(5));
            $order = Order::create([
                'user_id'       => auth()->id(),
                'customer_name' => $request->customer_name,
                'order_number'  => $orderNumber,
                'total_amount'  => $totalAmount,
                'tax'           => $tax,
                'discount'      => 0,
                'status'        => 'pending',
                'type'          => 'offline',
            ]);

            foreach ($orderItemsData as &$itemData) {
                $itemData['order_id'] = $order->id;
            }
            OrderItem::insert($orderItemsData);

            if ($isCash) {
                $change = $request->amount_paid - $totalAmount;

                Payment::create([
                    'order_id'       => $order->id,
                    'payment_method' => 'cash',
                    'amount_paid'    => $request->amount_paid,
                    'change'         => $change,
                    'status'         => 'success',
                    'gross_amount'   => $totalAmount,
                ]);

                DB::commit();

                return response()->json([
                    'success'        => true,
                    'payment_method' => 'cash',
                    'message'        => 'Pesanan berhasil dibuat. Status: Menunggu Konfirmasi Kasir.',
                    'change'         => $change,
                    'order_number'   => $order->order_number,
                ]);
            } elseif ($request->payment_method === 'qris') {
                $qrUrl = null;
                $transactionId = null;
                $isMock = false;

                try {
                    $chargeResponse = $midtransService->createQrisCharge($order, $itemsListForMidtrans);

                    if (isset($chargeResponse->actions)) {
                        foreach ($chargeResponse->actions as $action) {
                            if ($action->name === 'generate-qr-code') {
                                $qrUrl = $action->url;
                                break;
                            }
                        }
                    }

                    if (! $qrUrl) {
                        throw new \Exception('Gagal memperoleh kode QR dari Midtrans Core API.');
                    }

                    $transactionId = $chargeResponse->transaction_id ?? null;
                } catch (\Exception $e) {
                    if (config('app.env') === 'local') {
                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($order->order_number);
                        $transactionId = 'MOCK-TX-' . strtoupper(Str::random(10));
                        $isMock = true;
                    } else {
                        throw $e;
                    }
                }

                $expiryTime = now()->addMinutes(15);

                Payment::create([
                    'order_id'           => $order->id,
                    'payment_method'     => 'qris',
                    'amount_paid'        => $totalAmount,
                    'change'             => 0,
                    'status'             => 'pending',
                    'transaction_id'     => $transactionId,
                    'payment_type'       => 'qris',
                    'gross_amount'       => $totalAmount,
                    'transaction_status' => 'pending',
                    'qr_url'             => $qrUrl,
                    'expiry_time'        => $expiryTime,
                ]);

                DB::commit();

                return response()->json([
                    'success'        => true,
                    'payment_method' => 'qris',
                    'message'        => $isMock ? 'QRIS berhasil dibuat (MOCK/SIMULASI)' : 'QRIS berhasil dibuat',
                    'qr_url'         => $qrUrl,
                    'order_number'   => $order->order_number,
                    'total'          => $totalAmount,
                    'expiry_time'    => $expiryTime->toIso8601String(),
                ]);
            } else {
                $snapToken = $midtransService->getSnapToken($order, $itemsListForMidtrans);

                Payment::create([
                    'order_id'       => $order->id,
                    'payment_method' => 'qris',
                    'amount_paid'    => $totalAmount,
                    'change'         => 0,
                    'status'         => 'pending',
                    'snap_token'     => $snapToken,
                    'gross_amount'   => $totalAmount,
                ]);

                DB::commit();

                return response()->json([
                    'success'        => true,
                    'payment_method' => 'midtrans',
                    'message'        => 'Token Midtrans berhasil dibuat',
                    'snap_token'     => $snapToken,
                    'order_number'   => $order->order_number,
                    'total'          => $totalAmount,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function printReceipt($orderNumber)
    {
        $order = Order::with(['items.product', 'payment'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('orders.receipt', compact('order'));
    }

    public function simulateSuccess($orderNumber)
    {
        if (config('app.env') !== 'local') {
            return response()->json(['success' => false, 'message' => 'Hanya diizinkan di local environment.'], 403);
        }

        try {
            DB::beginTransaction();
            $order = Order::where('order_number', $orderNumber)->firstOrFail();
            $order->status = 'completed';
            $order->save();

            $payment = Payment::firstOrNew(['order_id' => $order->id]);
            $payment->fill([
                'status'             => 'success',
                'transaction_status' => 'settlement',
            ]);
            $payment->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Simulasi pembayaran sukses berhasil.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
