<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }} | Art Coffee</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #140E0C; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .glass-panel { 
            background: rgba(255, 255, 255, 0.02); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.35);
        }
        .glow-gold {
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.15);
        }
    </style>
</head>
<body class="text-gray-200 min-h-screen py-12 px-4 sm:px-6 lg:px-8 flex flex-col justify-between" x-data="invoiceData('{{ $order->order_number }}', '{{ $order->status }}')">
    
    <div class="max-w-3xl w-full mx-auto glass-panel rounded-3xl overflow-hidden glow-gold p-6 sm:p-10 space-y-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-white/10 pb-6 gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-yellow-200 uppercase">Art Coffee</h1>
                <p class="text-xs text-gray-500 tracking-widest mt-1">INVOICE DIGITAL</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-xs uppercase tracking-wider text-gray-500">Nomor Invoice</span>
                <h2 class="text-xl font-bold text-amber-500 mt-0.5">{{ $order->order_number }}</h2>
            </div>
        </div>

        <!-- Status Panel -->
        <div class="p-6 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-6 transition-all duration-500" 
             :class="status === 'completed' ? 'bg-green-500/10 border border-green-500/30' : (status === 'cancelled' ? 'bg-red-500/10 border border-red-500/30' : (status === 'processing' ? 'bg-blue-500/10 border border-blue-500/30' : 'bg-amber-500/10 border border-amber-500/30'))">
            <div class="flex items-center gap-4">
                <div class="w-4 h-4 rounded-full animate-pulse"
                     :class="status === 'completed' ? 'bg-green-500 shadow-[0_0_10px_#22c55e]' : (status === 'cancelled' ? 'bg-red-500 shadow-[0_0_10px_#ef4444]' : (status === 'processing' ? 'bg-blue-500 shadow-[0_0_10px_#3b82f6]' : 'bg-amber-500 shadow-[0_0_10px_#f59e0b]'))"></div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Status Pemesanan</p>
                    <p class="font-black text-xl tracking-wide capitalize"
                       :class="status === 'completed' ? 'text-green-400' : (status === 'cancelled' ? 'text-red-400' : (status === 'processing' ? 'text-blue-400' : 'text-amber-400'))"
                       x-text="status === 'completed' ? 'Selesai / Siap Diambil' : (status === 'cancelled' ? 'Dibatalkan' : (status === 'processing' ? 'Sedang Diproses' : 'Menunggu Antrean'))"></p>
                </div>
            </div>
            <div class="text-center md:text-right">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Metode Pembayaran</p>
                <p class="font-extrabold text-white uppercase tracking-wider mt-0.5">
                    {{ $order->payment ? ($order->payment->payment_method === 'cash' ? 'Tunai (Cash)' : str_replace('_', ' ', $order->payment->payment_type ?? 'Midtrans QRIS')) : 'Midtrans QRIS' }}
                </p>
            </div>
        </div>

        <!-- Order Metadata Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-white/5 pb-8">
            <div class="space-y-1">
                <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider">Detail Pelanggan</h3>
                <p class="text-base font-bold text-white">{{ $order->customer_name ?? ($order->user ? $order->user->name : 'Pelanggan Offline') }}</p>
                <p class="text-sm text-gray-400">Tipe Pesanan: <span class="capitalize font-semibold text-white">Offline (Di Toko / Table QR)</span></p>
            </div>
            <div class="space-y-1 sm:text-right">
                <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider">Tanggal & Waktu</h3>
                <p class="text-base font-bold text-white">{{ $order->created_at->format('d F Y') }}</p>
                <p class="text-sm text-gray-400">{{ $order->created_at->format('H:i') }} WIB</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider">Daftar Menu Yang Dipesan</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 text-gray-400 text-xs uppercase tracking-wider">
                            <th class="py-3">Menu</th>
                            <th class="py-3 text-center">Jumlah</th>
                            <th class="py-3 text-right">Harga Satuan</th>
                            <th class="py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($order->items as $item)
                            <tr class="text-sm">
                                <td class="py-4 font-semibold text-white">{{ $item->product->name }}</td>
                                <td class="py-4 text-center text-amber-400 font-bold">{{ $item->quantity }}</td>
                                <td class="py-4 text-right text-gray-300">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="py-4 text-right font-bold text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Totals -->
        <div class="pt-6 border-t border-white/10 flex justify-end">
            <div class="w-full sm:w-80 space-y-3">
                <div class="flex justify-between text-sm text-gray-400">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->total_amount - $order->tax, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-400">
                    <span>Pajak Restoran (10%)</span>
                    <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-lg font-bold text-white border-t border-white/10 pt-3">
                    <span class="text-amber-500 font-extrabold">Total Pembayaran</span>
                    <span class="text-xl font-black text-amber-500">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 pt-6">
            <a href="{{ route('home') }}" class="w-full sm:w-auto px-8 py-3.5 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-full font-bold text-center transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Beranda
            </a>
            
            <!-- Midtrans Pay button if status is still pending -->
            <template x-if="status === 'pending' && '{{ $order->payment && $order->payment->snap_token ? 1 : 0 }}' == '1'">
                <button @click="payNow" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-coffee-900 rounded-full font-black text-center shadow-[0_0_20px_rgba(212,175,55,0.3)] transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Bayar Sekarang
                </button>
            </template>

            <!-- Print Thermal Receipt button for cashier or print layout -->
            <button @click="printInvoice" class="w-full sm:w-auto px-8 py-3.5 bg-amber-600/20 hover:bg-amber-600/30 border border-amber-500/30 text-amber-400 rounded-full font-bold text-center transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Struk POS
            </button>
        </div>

    </div>

    <!-- Footer Credit -->
    <div class="mt-8 text-center text-xs text-gray-600">
        <p>&copy; {{ date('Y') }} Art Coffee Restaurant. Semua hak dilindungi.</p>
        <p class="mt-1">Halaman ini akan mendeteksi status pembayaran dan proses order secara otomatis secara real-time.</p>
    </div>

    <script>
        function invoiceData(orderNumber, initialStatus) {
            return {
                orderNumber: orderNumber,
                status: initialStatus,
                pollingInterval: null,

                init() {
                    if (this.status === 'pending' || this.status === 'processing') {
                        // Start real-time status polling
                        this.pollingInterval = setInterval(() => {
                            this.checkPaymentStatus();
                        }, 3000);
                    }
                },

                async checkPaymentStatus() {
                    try {
                        const response = await axios.get(`/orders/${this.orderNumber}/status`);
                        if (response.data) {
                            const prevStatus = this.status;
                            this.status = response.data.status;
                            
                            // Only stop polling if final state (completed or cancelled) is reached
                            if (this.status === 'completed' || this.status === 'cancelled') {
                                clearInterval(this.pollingInterval);
                                
                                if (this.status === 'completed') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Pesanan Selesai!',
                                        text: 'Terima kasih, pesanan Anda telah selesai dibuat dan siap diambil.',
                                        confirmButtonColor: '#D4AF37',
                                        background: '#1C1513',
                                        color: '#fff'
                                    });
                                } else if (this.status === 'cancelled') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Pesanan Dibatalkan',
                                        text: 'Pesanan ini telah dibatalkan.',
                                        confirmButtonColor: '#D4AF37',
                                        background: '#1C1513',
                                        color: '#fff'
                                    });
                                }
                            } else if (this.status === 'processing' && prevStatus !== 'processing') {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Sedang Diproses',
                                    text: 'Barista kami sedang menyiapkan pesanan Anda.',
                                    confirmButtonColor: '#D4AF37',
                                    background: '#1C1513',
                                    color: '#fff',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 5000,
                                    showConfirmButton: false
                                });
                            }
                        }
                    } catch (error) {
                        console.error('Gagal mengecek status pembayaran:', error);
                    }
                },

                payNow() {
                    const snapToken = '{{ $order->payment ? $order->payment->snap_token : '' }}';
                    if (!snapToken) return;

                    window.snap.pay(snapToken, {
                        onSuccess: (result) => {
                            this.status = 'completed';
                            clearInterval(this.pollingInterval);
                            Swal.fire({ icon: 'success', title: 'Sukses!', text: 'Pembayaran berhasil.', confirmButtonColor: '#D4AF37' });
                        },
                        onPending: (result) => {
                            Swal.fire({ icon: 'info', title: 'Pending', text: 'Silakan selesaikan pembayaran.', confirmButtonColor: '#D4AF37' });
                        },
                        onError: (result) => {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Pembayaran gagal.', confirmButtonColor: '#D4AF37' });
                        }
                    });
                },

                printInvoice() {
                    window.open('/kasir/orders/' + this.orderNumber + '/receipt', 'Cetak Struk', 'width=400,height=600');
                }
            }
        }
    </script>
</body>
</html>
