<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $products = Product::with('category')->where('status', '!=', 'sold_out')->get();
        $testimonials = Review::with('user')->where('is_featured', true)->orWhere('rating', '>=', 4)->take(6)->get();

        return view('welcome', compact('categories', 'products', 'testimonials'));
    }

    public function processCheckout(Request $request, MidtransService $midtransService)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
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
                    throw new \Exception("Maaf, stok {$product->name} tidak mencukupi.");
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
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $itemSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $itemsListForMidtrans[] = [
                    'id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'name' => $product->name,
                ];
            }

            $tax = $subtotal * 0.10;
            $totalAmount = $subtotal + $tax;

            // Generate professional invoice number (offline type)
            $orderNumber = 'INV-'.now()->format('Ymd').'-OFF-'.strtoupper(Str::random(5));

            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name ?? 'Pelanggan Meja',
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'tax' => $tax,
                'discount' => 0,
                'status' => 'pending',
                'type' => 'offline',
            ]);

            foreach ($orderItemsData as &$itemData) {
                $itemData['order_id'] = $order->id;
            }
            OrderItem::insert($orderItemsData);

            // Generate Midtrans Snap Token
            $snapToken = $midtransService->getSnapToken($order, $itemsListForMidtrans);

            // Save Payment Record
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'qris',
                'amount_paid' => $totalAmount,
                'change' => 0,
                'status' => 'pending',
                'snap_token' => $snapToken,
                'gross_amount' => $totalAmount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.',
                'order_number' => $order->order_number,
                'snap_token' => $snapToken,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function processQrisCheckout(Request $request, MidtransService $midtransService)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
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
                    throw new \Exception("Maaf, stok {$product->name} tidak mencukupi.");
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
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $itemSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $itemsListForMidtrans[] = [
                    'id' => $product->id,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'name' => $product->name,
                ];
            }

            $tax = $subtotal * 0.10;
            $totalAmount = $subtotal + $tax;

            $orderNumber = 'INV-'.now()->format('Ymd').'-OFF-'.strtoupper(Str::random(5));

            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name ?? 'Pelanggan Meja',
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'tax' => $tax,
                'discount' => 0,
                'status' => 'pending',
                'type' => 'offline',
            ]);

            foreach ($orderItemsData as &$itemData) {
                $itemData['order_id'] = $order->id;
            }
            OrderItem::insert($orderItemsData);

            $chargeResponse = $midtransService->createQrisCharge($order, $itemsListForMidtrans);

            $qrUrl = null;
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

            $expiryTime = now()->addMinutes(15);

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'qris',
                'amount_paid' => $totalAmount,
                'change' => 0,
                'status' => 'pending',
                'transaction_id' => $chargeResponse->transaction_id ?? null,
                'payment_type' => 'qris',
                'gross_amount' => $totalAmount,
                'transaction_status' => 'pending',
                'qr_url' => $qrUrl,
                'expiry_time' => $expiryTime,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'QRIS berhasil dibuat. Silakan lakukan pembayaran.',
                'order_number' => $order->order_number,
                'total_amount' => $totalAmount,
                'qr_url' => $qrUrl,
                'expiry_time' => $expiryTime->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
