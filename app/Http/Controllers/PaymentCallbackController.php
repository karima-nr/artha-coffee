<?php

namespace App\Http\Controllers;

use App\Mail\PaymentSuccessMail;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentCallbackController extends Controller
{
    public function handleCallback(Request $request)
    {
        $payload = $request->all();

        // 1. Log incoming notification (useful for debugging)
        Log::info('Midtrans Webhook Callback Received:', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        $transactionId = $payload['transaction_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $transactionTime = $payload['transaction_time'] ?? null;

        if (! $orderId || ! $statusCode || ! $grossAmount || ! $signatureKey) {
            return response()->json(['success' => false, 'message' => 'Payload tidak lengkap.'], 400);
        }

        // 2. Webhook Signature Verification
        $serverKey = config('midtrans.server_key');
        $localSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($localSignature !== $signatureKey) {
            Log::warning("Midtrans Webhook Invalid Signature detected for Order ID: {$orderId}");

            return response()->json(['success' => false, 'message' => 'Tanda tangan tidak valid.'], 403);
        }

        // 3. Process Order status
        $order = Order::with(['items.product', 'user'])->where('order_number', $orderId)->first();

        if (! $order) {
            Log::error("Midtrans Webhook Order not found for Order ID: {$orderId}");

            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Avoid double processing
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json(['success' => true, 'message' => 'Transaksi sudah diproses sebelumnya.']);
        }

        $payment = Payment::firstOrNew(['order_id' => $order->id]);

        // Map Midtrans statuses to application statuses
        $paymentStatus = 'pending';
        $orderStatus = 'pending';
        $shouldRestoreStock = false;
        $isSuccess = false;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $paymentStatus = 'pending';
                $orderStatus = 'pending';
            } elseif ($fraudStatus == 'accept') {
                $paymentStatus = 'success';
                $orderStatus = 'completed';
                $isSuccess = true;
            }
        } elseif ($transactionStatus == 'settlement') {
            $paymentStatus = 'success';
            $orderStatus = 'completed';
            $isSuccess = true;
        } elseif ($transactionStatus == 'pending') {
            $paymentStatus = 'pending';
            $orderStatus = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $paymentStatus = 'failed';
            $orderStatus = 'cancelled';
            $shouldRestoreStock = true;
        }

        // Update Payment Record
        $payment->fill([
            'transaction_id' => $transactionId,
            'payment_type' => $paymentType,
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'transaction_time' => $transactionTime,
            'fraud_status' => $fraudStatus,
            'status' => $paymentStatus,
            'payment_method' => $paymentType ?? 'qris',
        ]);
        $payment->save();

        // Update Order Record
        $order->status = $orderStatus;
        $order->save();

        // 4. Stock auto-sync restoration if payment failed/cancelled/expired
        if ($shouldRestoreStock) {
            Log::info("Restoring stock for cancelled/expired Order: {$order->order_number}");
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->quantity;
                    // Reset status based on new stock level
                    if ($product->stock > 10) {
                        $product->status = 'ready';
                    } elseif ($product->stock > 0) {
                        $product->status = 'limited';
                    }
                    $product->save();
                }
            }
        }

        // 5. Send email notification on successful payment
        if ($isSuccess && $order->user && $order->user->email) {
            try {
                Mail::to($order->user->email)->send(new PaymentSuccessMail($order));
                Log::info("Payment success email sent to customer: {$order->user->email}");
            } catch (\Exception $e) {
                Log::error('Failed to send payment success email: '.$e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => 'Notifikasi berhasil diproses.']);
    }
}
