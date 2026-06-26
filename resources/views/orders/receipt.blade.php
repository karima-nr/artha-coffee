<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk POS - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 10px;
            background: #fff;
            width: 80mm; /* Standard thermal printer width */
            box-sizing: border-box;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .header {
            margin-bottom: 15px;
        }
        .header .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .header .subtitle {
            font-size: 10px;
            margin: 2px 0 0 0;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .info-table, .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            font-size: 10px;
            padding: 2px 0;
        }
        .items-table th {
            border-bottom: 1px dashed #000;
            font-size: 10px;
            padding-bottom: 5px;
            text-align: left;
        }
        .items-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .items-table .qty {
            text-align: center;
            width: 10%;
        }
        .items-table .price {
            text-align: right;
            width: 30%;
        }
        .totals-table {
            width: 100%;
            margin-top: 5px;
        }
        .totals-table td {
            padding: 2px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 9px;
        }
        .no-print-btn {
            display: block;
            width: 100%;
            padding: 8px;
            background: #000;
            color: #fff;
            border: none;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        @media print {
            .no-print-btn {
                display: none;
            }
            body {
                width: 100%;
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Native Print Button (Hidden during print) -->
    <button onclick="window.print()" class="no-print-btn">CETAK STRUK</button>

    <div class="header text-center">
        <h1 class="title">ART COFFEE</h1>
        <p class="subtitle">Jl. Coffee Garden No. 45, Jakarta</p>
        <p class="subtitle">Telp: (021) 555-8889</p>
    </div>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td>No. Invoice:</td>
            <td class="text-right bold">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td>Tanggal:</td>
            <td class="text-right">{{ \Carbon\Carbon::parse($order->created_at)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
        </tr>
        <tr>
            <td>Jam:</td>
            <td class="text-right">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td>Kasir:</td>
            <td class="text-right">{{ $order->user ? $order->user->name : 'POS Kasir' }}</td>
        </tr>
        <tr>
            <td>Metode:</td>
            <td class="text-right bold" style="text-transform: uppercase;">
                {{ $order->payment ? ($order->payment->payment_method === 'cash' ? 'CASH' : str_replace('_', ' ', $order->payment->payment_type ?? 'QRIS')) : 'QRIS' }}
            </td>
        </tr>
        <tr>
            <td>Status:</td>
            <td class="text-right bold">{{ $order->status === 'completed' ? 'LUNAS' : 'PENDING' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Menu</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product->name }}<br>
                        <span style="font-size: 9px; color: #555;">@ Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    </td>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">Rp {{ number_format($order->total_amount - $order->tax, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pajak Restoran (10%):</td>
            <td class="text-right">Rp {{ number_format($order->tax, 0, ',', '.') }}</td>
        </tr>
        <tr class="bold" style="font-size: 13px;">
            <td>TOTAL:</td>
            <td class="text-right">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
        </tr>
        @if($order->payment && $order->payment->payment_method === 'cash')
            <tr class="divider-row">
                <td colspan="2"><div style="border-top: 1px dashed #000; margin: 4px 0;"></div></td>
            </tr>
            <tr>
                <td>Dibayar:</td>
                <td class="text-right">Rp {{ number_format($order->payment->amount_paid, 0, ',', '.') }}</td>
            </tr>
            <tr class="bold">
                <td>Kembali:</td>
                <td class="text-right">Rp {{ number_format($order->payment->change, 0, ',', '.') }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    <div class="footer text-center">
        <p class="bold">TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
        <p>Silakan Datang Kembali!</p>
        <p style="font-size: 8px; margin-top: 5px;">Powered by Art Coffee System</p>
    </div>

    <script>
        // Trigger browser print screen automatically on load
        window.addEventListener('DOMContentLoaded', () => {
            // Short delay to allow fonts/styles to stabilize
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
