<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function getSnapToken(Order $order, array $itemsList)
    {
        $subtotal = 0;
        $itemDetails = [];

        foreach ($itemsList as $item) {
            $price = (int) $item['price'];
            $qty = (int) $item['quantity'];
            $subtotal += $price * $qty;

            $itemDetails[] = [
                'id' => 'PROD-'.$item['id'],
                'price' => $price,
                'quantity' => $qty,
                'name' => substr($item['name'], 0, 50),
            ];
        }

        $taxAmount = (int) ($subtotal * 0.10);
        if ($taxAmount > 0) {
            $itemDetails[] = [
                'id' => 'TAX-10',
                'price' => $taxAmount,
                'quantity' => 1,
                'name' => 'Pajak Restoran (10%)',
            ];
        }

        $totalAmount = $subtotal + $taxAmount;

        $transactionDetails = [
            'order_id' => $order->order_number,
            'gross_amount' => $totalAmount,
        ];

        $customerDetails = [
            'first_name' => ($order->type === 'offline' && ! empty($order->customer_name)) ? $order->customer_name : ($order->user ? $order->user->name : 'Pelanggan Cafe'),
            'email' => ($order->type === 'offline') ? 'customer@artcoffee.com' : ($order->user ? $order->user->email : 'customer@artcoffee.com'),
        ];

        $payload = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'callbacks' => [
                'finish' => route('orders.invoice', $order->order_number),
            ],
        ];

        return Snap::getSnapToken($payload);
    }

    public function createQrisCharge(Order $order, array $itemsList)
    {
        $subtotal = 0;
        $itemDetails = [];

        foreach ($itemsList as $item) {
            $price = (int) $item['price'];
            $qty = (int) $item['quantity'];
            $subtotal += $price * $qty;

            $itemDetails[] = [
                'id' => 'PROD-'.$item['id'],
                'price' => $price,
                'quantity' => $qty,
                'name' => substr($item['name'], 0, 50),
            ];
        }

        $taxAmount = (int) ($subtotal * 0.10);
        if ($taxAmount > 0) {
            $itemDetails[] = [
                'id' => 'TAX-10',
                'price' => $taxAmount,
                'quantity' => 1,
                'name' => 'Pajak Restoran (10%)',
            ];
        }

        $totalAmount = $subtotal + $taxAmount;

        $transactionDetails = [
            'order_id' => $order->order_number,
            'gross_amount' => $totalAmount,
        ];

        $customerDetails = [
            'first_name' => ($order->type === 'offline' && ! empty($order->customer_name)) ? $order->customer_name : ($order->user ? $order->user->name : 'Pelanggan Cafe'),
            'email' => ($order->type === 'offline') ? 'customer@artcoffee.com' : ($order->user ? $order->user->email : 'customer@artcoffee.com'),
        ];

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        return CoreApi::charge($payload);
    }
}
