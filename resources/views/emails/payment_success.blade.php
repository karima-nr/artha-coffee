<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pembayaran Sukses | Art Coffee</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #120e0c;
            color: #d1c7bd;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: rgba(30, 24, 21, 0.95);
            border-radius: 12px;
            border: 1px solid #d4af37;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #4a3b32;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .title {
            font-size: 20px;
            color: #ffffff;
            margin-top: 10px;
        }
        .order-info {
            background-color: #261f1c;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #d4af37;
        }
        .order-info table {
            width: 100%;
        }
        .order-info td {
            padding: 5px 0;
        }
        .order-info td.label {
            color: #8c7b70;
            width: 40%;
        }
        .order-info td.value {
            color: #ffffff;
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            text-align: left;
            border-bottom: 2px solid #4a3b32;
            padding: 10px 5px;
            color: #d4af37;
            font-size: 14px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 12px 5px;
            border-bottom: 1px solid #2e2420;
            font-size: 14px;
            color: #ffffff;
        }
        .items-table td.qty {
            color: #d4af37;
            text-align: center;
        }
        .items-table td.price {
            text-align: right;
        }
        .totals {
            margin-top: 10px;
            border-top: 2px dashed #4a3b32;
            padding-top: 15px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 5px 0;
            color: #d1c7bd;
        }
        .total-row.grand {
            font-size: 18px;
            font-weight: bold;
            color: #d4af37;
            border-top: 1px solid #4a3b32;
            padding-top: 10px;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #8c7b70;
            margin-top: 30px;
            border-top: 1px solid #2e2420;
            padding-top: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #d4af37;
            color: #120e0c !important;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 0;
            text-align: center;
        }
        .btn:hover {
            background-color: #f3cf5a;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Art Coffee</div>
            <div class="title">Pembayaran Sukses Terverifikasi</div>
        </div>

        <p>Halo <strong>{{ $order->user ? $order->user->name : 'Pelanggan Setia' }}</strong>,</p>
        <p>Terima kasih atas pesanan Anda di Art Coffee! Pembayaran untuk transaksi Anda telah berhasil kami terima dan verifikasi.</p>

        <div class="order-info">
            <table style="width: 100%;">
                <tr>
                    <td class="label">No. Invoice</td>
                    <td class="value">{{ $order->order_number }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Transaksi</td>
                    <td class="value">{{ $order->created_at->format('d M Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Status Pembayaran</td>
                    <td class="value" style="color: #4cd137;">SUKSES (LUNAS)</td>
                </tr>
                <tr>
                    <td class="label">Metode Pembayaran</td>
                    <td class="value" style="text-transform: uppercase;">{{ $order->payment ? $order->payment->payment_type : 'Midtrans QRIS' }}</td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Harga</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td><strong>{{ $item->product->name }}</strong></td>
                        <td class="qty">{{ $item->quantity }}</td>
                        <td class="price">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="price">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row" style="display: table; width: 100%; margin-bottom: 5px;">
                <span style="display: table-cell; text-align: left;">Subtotal</span>
                <span style="display: table-cell; text-align: right;">Rp {{ number_format($order->total_amount - $order->tax, 0, ',', '.') }}</span>
            </div>
            <div class="total-row" style="display: table; width: 100%; margin-bottom: 5px;">
                <span style="display: table-cell; text-align: left;">Pajak Restoran (10%)</span>
                <span style="display: table-cell; text-align: right;">Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
            </div>
            <div class="total-row grand" style="display: table; width: 100%; border-top: 1px solid #4a3b32; padding-top: 10px; margin-top: 5px;">
                <span style="display: table-cell; text-align: left; font-weight: bold; color: #d4af37; font-size: 18px;">Total Pembayaran</span>
                <span style="display: table-cell; text-align: right; font-weight: bold; color: #d4af37; font-size: 18px;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div style="text-align: center; margin-top: 25px;">
            <a href="{{ route('orders.invoice', $order->order_number) }}" class="btn">Lihat Invoice Digital</a>
        </div>

        <p style="margin-top: 25px;">Pesanan Anda saat ini sedang disiapkan oleh barista kami. Silakan tunjukkan Invoice Digital atau email ini jika Anda makan di tempat (Dine-in) atau mengambil langsung (Takeaway).</p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Art Coffee Restaurant. Semua hak dilindungi.</p>
            <p>Jl. Coffee Garden No. 45, Jakarta, Indonesia</p>
        </div>
    </div>
</body>
</html>
