<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan Art Coffee - {{ $monthLabel }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; background-color: #fff; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0 0; color: #666; font-size: 14px; }
        .period-info { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 10px 16px; margin-bottom: 20px; font-size: 12px; color: #92400e; }
        .divider { border-top: 2px solid #333; margin: 15px 0; }
        .kpi-container { display: flex; justify-content: space-between; margin-bottom: 30px; gap: 15px; }
        .kpi-card { flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 15px; text-align: center; background-color: #f9f9f9; }
        .kpi-card .label { font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .kpi-card .value { font-size: 18px; font-weight: bold; color: #b45309; }
        .report-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .report-table th, .report-table td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 12px; }
        .report-table th { background-color: #f3f4f6; font-weight: bold; }
        .report-table tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .signature-container { display: flex; justify-content: flex-end; margin-top: 50px; }
        .signature-box { text-align: center; width: 200px; }
        .signature-space { height: 70px; }
        .no-print-btn { display: inline-block; background: #b45309; color: #fff; padding: 10px 20px; border: none; font-weight: bold; border-radius: 6px; cursor: pointer; margin-bottom: 20px; }
        @media print { .no-print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print-btn">CETAK LAPORAN</button>

    <div class="header">
        <h1>ART COFFEE RESTAURANT</h1>
        <p>Laporan Rekap Penjualan Bulanan &mdash; {{ $monthLabel }}</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}, Pukul {{ \Carbon\Carbon::now()->format('H:i') }} WIB</p>
    </div>

    <div class="divider"></div>

    <div class="period-info">
        📅 <strong>Periode Laporan:</strong>
        {{ \Carbon\Carbon::parse($period['start'])->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
        s/d
        {{ \Carbon\Carbon::parse($period['end'])->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
        @if($period['last_reset'])
            &nbsp;&mdash;&nbsp;
            <strong>Reset dilakukan pada:</strong>
            {{ $period['last_reset']->reset_at->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB
            oleh {{ $period['last_reset']->user?->name ?? 'Admin' }}
            @if($period['last_reset']->note)
                ({{ $period['last_reset']->note }})
            @endif
        @endif
    </div>

    <div class="kpi-container">
        <div class="kpi-card">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="kpi-card">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ $totalOrders }} Pesanan</div>
        </div>
        <div class="kpi-card">
            <div class="label">Rata-rata Transaksi</div>
            <div class="value">Rp {{ number_format($averageOrder, 0, ',', '.') }}</div>
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">No. Order</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 20%;">Pelanggan</th>
                <th style="width: 10%;">Tipe</th>
                <th style="width: 15%;">Metode</th>
                <th class="text-right" style="width: 20%;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="bold">{{ $order->order_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->locale('id')->isoFormat('D MMM Y, HH:mm') }} WIB</td>
                    <td>{{ $order->user ? $order->user->name : 'POS Kasir' }}</td>
                    <td class="text-center" style="text-transform: uppercase;">{{ $order->type }}</td>
                    <td>
                        {{ $order->payment
                            ? ($order->payment->payment_method === 'cash'
                                ? 'Cash'
                                : strtoupper(str_replace('_', ' ', $order->payment->payment_type ?? 'QRIS')))
                            : 'QRIS' }}
                    </td>
                    <td class="text-right bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
            <tr class="bold" style="background-color: #f3f4f6;">
                <td colspan="6" class="text-right">TOTAL REVENUE BULAN {{ strtoupper($monthLabel) }}:</td>
                <td class="text-right" style="color: #b45309; font-size: 13px;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-container">
        <div class="signature-box">
            <p>{{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</p>
            <p class="bold">Manajer Keuangan</p>
            <div class="signature-space"></div>
            <div style="border-top: 1px solid #000; font-weight: bold; padding-top: 5px;">{{ auth()->user()->name }}</div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>
