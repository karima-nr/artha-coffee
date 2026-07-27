<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZCoffee Hening - POS Kasir</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        // Setup CSRF token untuk semua request Axios
        document.addEventListener('DOMContentLoaded', function () {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (token) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
            }
        });
    </script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans h-screen overflow-hidden flex flex-col" x-data="posSystem()">
    
    <!-- FIX: Inline Tailwind responsive classes for environments where Vite (npm run dev) is not running -->
    <style>
        .min-h-0 { min-height: 0px !important; }
        .min-h-\[64px\] { min-height: 64px !important; }
        @media (min-width: 1024px) {
            .lg\:flex { display: flex !important; }
            .lg\:hidden { display: none !important; }
            .lg\:inline { display: inline !important; }
            .lg\:flex-row { flex-direction: row !important; }
            .lg\:flex-none { flex: none !important; }
            .lg\:w-auto { width: auto !important; }
            .lg\:w-96 { width: 24rem !important; }
            .lg\:border-t-0 { border-top-width: 0px !important; }
            .lg\:border-l { border-left-width: 1px !important; }
            .lg\:p-6 { padding: 1.5rem !important; }
            .lg\:p-5 { padding: 1.25rem !important; }
            .lg\:py-0 { padding-top: 0px !important; padding-bottom: 0px !important; }
            .lg\:px-6 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
            .lg\:h-16 { height: 4rem !important; }
            .lg\:mt-0 { margin-top: 0px !important; }
            .lg\:justify-center { justify-content: center !important; }
            .lg\:space-y-4 > :not([hidden]) ~ :not([hidden]) { --tw-space-y-reverse: 0; margin-top: calc(1rem * calc(1 - var(--tw-space-y-reverse))); margin-bottom: calc(1rem * var(--tw-space-y-reverse)); }
        }
        @media (min-width: 768px) {
            .md\:block { display: block !important; }
            .md\:gap-2 { gap: 0.5rem !important; }
            .md\:gap-4 { gap: 1rem !important; }
            .md\:p-1\.5 { padding: 0.375rem !important; }
            .md\:px-5 { padding-left: 1.25rem !important; padding-right: 1.25rem !important; }
            .md\:px-6 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
            .md\:py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
            .md\:py-0 { padding-top: 0px !important; padding-bottom: 0px !important; }
            .md\:text-sm { font-size: 0.875rem !important; line-height: 1.25rem !important; }
            .md\:text-xl { font-size: 1.25rem !important; line-height: 1.75rem !important; }
            .md\:text-2xl { font-size: 1.5rem !important; line-height: 2rem !important; }
        }
    </style>

    <!-- Header -->
    <header class="bg-white shadow-md min-h-[64px] py-3 lg:py-0 lg:h-16 flex flex-col lg:flex-row items-center justify-between px-4 lg:px-6 z-10 border-b border-gray-200">
        <div class="flex items-center justify-between w-full lg:w-auto">
            <div class="flex items-center gap-3">
                <h1 class="text-lg md:text-xl font-bold text-amber-700">Art Coffee POS</h1>
                <span class="hidden md:block text-xs text-gray-400 font-semibold bg-gray-100 px-3 py-1 rounded-full" x-text="currentTime"></span>
            </div>
            <!-- Mobile User & Logout -->
            <div class="flex lg:hidden items-center gap-2">
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-gray-600 hover:text-amber-700 transition p-1">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 transition bg-red-50 px-2 py-1 rounded-md border border-red-200">Keluar</button>
                </form>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-1 md:gap-2 bg-gray-100 p-1 md:p-1.5 rounded-xl border border-gray-200 shadow-inner w-full lg:w-auto mt-3 lg:mt-0 overflow-x-auto justify-between lg:justify-center">
            <button @click="activeTab = 'pos'" :class="(activeTab === 'pos' || (activeTab === 'cart' && window.innerWidth >= 1024)) ? 'bg-white text-amber-800 font-extrabold shadow-sm' : 'text-gray-500 font-semibold hover:text-amber-700'" class="px-3 md:px-5 py-1.5 md:py-2 rounded-lg text-xs md:text-sm transition-all duration-200 flex-1 lg:flex-none text-center whitespace-nowrap">
                <span class="lg:hidden">Produk</span>
                <span class="hidden lg:inline">POS Kasir</span>
            </button>
            <button @click="activeTab = 'cart'" class="lg:hidden relative px-3 py-1.5 rounded-lg text-xs transition-all duration-200 flex-1 text-center whitespace-nowrap" :class="activeTab === 'cart' ? 'bg-white text-amber-800 font-extrabold shadow-sm' : 'text-gray-500 font-semibold hover:text-amber-700'">
                Keranjang
                <span x-show="cart.length > 0" class="absolute top-0.5 right-1 bg-amber-600 text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white" x-text="cart.length"></span>
            </button>
            <button @click="activeTab = 'orders'; fetchActiveOrders();" :class="activeTab === 'orders' ? 'bg-white text-amber-800 font-extrabold shadow-sm' : 'text-gray-500 font-semibold hover:text-amber-700'" class="px-3 md:px-5 py-1.5 md:py-2 rounded-lg text-xs md:text-sm transition-all duration-200 relative flex-1 lg:flex-none text-center whitespace-nowrap">
                <span class="lg:hidden">Antrean</span>
                <span class="hidden lg:inline">Antrean Pesanan</span>
                <span x-show="activeOrdersCount > 0" class="absolute top-0.5 right-1 bg-red-500 text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center border border-white animate-pulse" x-text="activeOrdersCount"></span>
            </button>
        </div>

        <div class="hidden lg:flex items-center gap-4">
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-amber-700 transition">Dashboard Admin</a>
            @endif
            <div class="flex items-center gap-2 bg-amber-50 px-3 py-1.5 rounded-full border border-amber-200">
                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                <span class="text-sm font-semibold text-amber-800">{{ auth()->user()->name }} (Kasir)</span>
            </div>
            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm font-bold text-red-600 hover:text-red-800 transition flex items-center gap-1 bg-red-50 hover:bg-red-100 border border-red-200/50 px-3.5 py-1.5 rounded-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Keluar
                </button>
            </form>
        </div>
    </header>

    <!-- Main POS Area -->
    <main class="flex-1 flex overflow-hidden">
        
        <!-- Tab 1 & Cart Mobile Tab: POS Kasir -->
        <div x-show="['pos', 'cart'].includes(activeTab)" class="flex-1 flex flex-col lg:flex-row overflow-hidden w-full">
            <!-- Products Grid -->
            <div :class="activeTab === 'pos' ? 'flex' : 'hidden lg:flex'" class="flex-1 flex-col bg-gray-50 p-4 lg:p-6 overflow-hidden">
                <!-- Categories Filter -->
                <div class="flex gap-2 mb-4 lg:mb-6 overflow-x-auto pb-2 scrollbar-hide">
                    <button @click="activeCategory = 'all'" :class="activeCategory === 'all' ? 'bg-amber-600 text-white border-amber-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'" class="px-4 lg:px-5 py-1.5 lg:py-2 rounded-full border text-sm lg:text-base font-semibold transition whitespace-nowrap">Semua</button>
                    @foreach($categories as $cat)
                    <button @click="activeCategory = '{{ $cat->id }}'" :class="activeCategory === '{{ $cat->id }}' ? 'bg-amber-600 text-white border-amber-600 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'" class="px-4 lg:px-5 py-1.5 lg:py-2 rounded-full border text-sm lg:text-base font-semibold transition whitespace-nowrap">{{ $cat->name }}</button>
                    @endforeach
                </div>

                <!-- Products -->
                <div class="flex-1 overflow-y-auto pr-2">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 lg:gap-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="product.stock > 0 && openProductModal(product)" :class="product.stock === 0 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:shadow-md hover:border-amber-300'" class="bg-white rounded-xl p-3 lg:p-4 border border-gray-200 shadow-sm transition group relative flex flex-col">
                                <div class="aspect-square bg-gray-100 rounded-lg mb-2 lg:mb-3 overflow-hidden">
                                    <template x-if="product.image">
                                        <img :src="'/Images/' + product.image" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    </template>
                                    <template x-if="!product.image">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-10 h-10 lg:w-12 lg:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    </template>
                                </div>
                                <h3 class="font-medium text-gray-900 leading-tight mb-1 text-sm lg:text-base" x-text="product.name"></h3>
                                <div class="mt-auto flex flex-col lg:flex-row lg:items-center justify-between gap-1 lg:gap-0">
                                    <span class="font-bold text-amber-700 whitespace-nowrap text-sm lg:text-base" x-text="'Rp. ' + Number(product.price).toLocaleString('id-ID')"></span>
                                    <span class="text-[10px] lg:text-xs px-2 py-0.5 lg:py-1 bg-gray-100 rounded-md font-medium w-max" :class="product.stock === 0 ? 'text-red-600 bg-red-50' : 'text-gray-600'" x-text="'Sisa: ' + product.stock"></span>
                                </div>
                                <template x-if="product.stock === 0">
                                    <div class="absolute inset-0 bg-white/60 backdrop-blur-[1px] flex items-center justify-center rounded-xl">
                                        <span class="bg-red-500 text-white font-bold px-3 py-1 rounded-full text-xs lg:text-sm shadow-sm rotate-[-10deg]">Habis</span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Cart Sidebar -->
            <div :class="activeTab === 'cart' ? 'flex flex-1' : 'hidden lg:flex'" class="w-full lg:w-96 lg:flex-none bg-white border-t lg:border-t-0 lg:border-l border-gray-200 flex-col shadow-[rgba(0,0,0,0.05)_-4px_0px_15px] h-full overflow-hidden">
                <div class="p-4 lg:p-5 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <h2 class="text-lg font-bold text-gray-800">Pesanan Saat Ini</h2>
                    <button @click="clearCart" x-show="cart.length > 0" class="text-sm font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto p-2 min-h-0">
                    <template x-if="cart.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-gray-400 space-y-3">
                            <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="text-sm">Belum ada pesanan</p>
                        </div>
                    </template>

                    <div class="space-y-2">
                        <template x-for="(item, index) in cart" :key="item.cartKey">
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 flex items-center justify-between group">
                                <div class="flex-1 pr-3">
                                    <h4 class="font-medium text-gray-800 text-sm leading-tight" x-text="item.name"></h4>
                                    <!-- Badge Ice / Hot (hanya tampil untuk minuman, bukan snack) -->
                                    <span x-show="item.type !== 'none'"
                                          class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-0.5 mb-1"
                                          :class="item.type === 'ice' ? 'bg-sky-100 text-sky-600' : 'bg-orange-100 text-orange-600'"
                                          x-text="item.type === 'ice' ? '🧊 Ice' : '☕ Hot'"></span>
                                    <p class="text-amber-600 font-semibold text-sm whitespace-nowrap" x-text="'Rp. ' + Number(item.price * item.quantity).toLocaleString('id-ID')"></p>
                                </div>
                                <div class="flex items-center gap-3 bg-white border border-gray-200 rounded-lg p-1">
                                    <button @click="updateQuantity(index, -1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 rounded-md transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></button>
                                    <span class="text-sm font-bold text-gray-800 w-4 text-center" x-text="item.quantity"></span>
                                    <button @click="updateQuantity(index, 1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 rounded-md transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Checkout Section -->
                <div class="bg-gray-50 border-t border-gray-200 p-4 lg:p-5 space-y-3 lg:space-y-4 shrink-0">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Nama Pelanggan / Nomor Meja</label>
                        <input type="text" x-model="customerName" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm font-medium" placeholder="Contoh: Meja 3 - Andi">
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span class="font-medium whitespace-nowrap" x-text="'Rp. ' + Number(subtotal).toLocaleString('id-ID')"></span>
                        </div>
                        <!-- Diskon 5% muncul saat subtotal >= 200.000 -->
                        <div x-show="discount > 0" class="flex justify-between text-sm font-semibold">
                            <span class="text-green-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M17 17h.01M7.586 16.414l8.828-8.828M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/></svg>
                                Diskon 5%
                            </span>
                            <span class="text-green-600 whitespace-nowrap" x-text="'- Rp. ' + Number(discount).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Pajak (10%)</span>
                            <span class="font-medium whitespace-nowrap" x-text="'Rp. ' + Number(tax).toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                            <span class="font-bold text-gray-800">Total</span>
                            <span class="text-2xl font-black text-amber-600 whitespace-nowrap" x-text="'Rp. ' + Number(total).toLocaleString('id-ID')"></span>
                        </div>
                        <!-- Banner diskon threshold -->
                        <div x-show="subtotal > 0 && subtotal < 200000" class="text-[11px] text-center text-amber-600 bg-amber-50 border border-amber-200 rounded-lg py-1.5 px-2">
                            🎉 Tambah <span class="font-bold" x-text="'Rp. ' + Number(200000 - subtotal).toLocaleString('id-ID')"></span> lagi untuk diskon 5%!
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <button @click="paymentMethod = 'cash'" :class="paymentMethod === 'cash' ? 'bg-amber-100 border-amber-500 text-amber-800' : 'bg-white border-gray-200 text-gray-600'" class="py-2 px-3 border rounded-lg font-medium text-sm flex flex-col items-center justify-center gap-1 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Tunai (Cash)
                        </button>
                        <button @click="paymentMethod = 'qris'" :class="paymentMethod === 'qris' ? 'bg-amber-100 border-amber-500 text-amber-800' : 'bg-white border-gray-200 text-gray-600'" class="py-2 px-3 border rounded-lg font-medium text-sm flex flex-col items-center justify-center gap-1 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            QRIS Langsung
                        </button>
                    </div>

                    <div x-show="paymentMethod === 'cash'">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Uang Diterima</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                            <input type="number" x-model.number="amountPaid" class="w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 font-medium" placeholder="0">
                        </div>
                        <div x-show="amountPaid > 0" class="flex justify-between items-center mt-2 text-sm">
                            <span class="text-gray-500">Kembalian:</span>
                            <span class="font-bold whitespace-nowrap" :class="change < 0 ? 'text-red-500' : 'text-green-600'" x-text="change >= 0 ? 'Rp. ' + Number(change).toLocaleString('id-ID') : 'Kurang Rp. ' + Number(Math.abs(change)).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <button @click="checkout" :disabled="cart.length === 0 || processing || (paymentMethod === 'cash' && amountPaid < total)" :class="(cart.length === 0 || processing || (paymentMethod === 'cash' && amountPaid < total)) ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-amber-600 text-white hover:bg-amber-700 shadow-md'" class="w-full py-3.5 rounded-xl font-bold text-lg flex items-center justify-center gap-2 transition">
                        <span x-show="!processing">Proses Transaksi</span>
                        <span x-show="processing" class="flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab 2: Antrean Pesanan -->
        <div x-show="activeTab === 'orders'" class="flex-1 flex flex-col bg-gray-50 p-4 lg:p-6 overflow-y-auto w-full" x-cloak>
            <div class="max-w-6xl mx-auto w-full space-y-4 lg:space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-200 pb-4 gap-3">
                    <div>
                        <h2 class="text-xl lg:text-2xl font-black text-gray-800">Daftar Antrean & Status Pesanan</h2>
                        <p class="text-xs text-gray-500 mt-1">Daftar pesanan offline toko yang sedang diproses barista</p>
                    </div>
                    <button @click="fetchActiveOrders()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold text-sm transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3m0 0l3 3m-3-3v8"></path></svg>
                        Segarkan
                    </button>
                </div>

                <!-- Active Orders Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="order in activeOrders" :key="order.id">
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between">
                            <!-- Order Header -->
                            <div class="p-5 border-b border-gray-100 bg-amber-50/50 flex justify-between items-start">
                                <div>
                                    <h3 class="font-extrabold text-amber-800 text-base" x-text="order.order_number"></h3>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="order.created_at + ' WIB'"></p>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wider border"
                                      :class="order.status === 'processing' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200'"
                                      x-text="order.status === 'processing' ? 'Proses' : 'Pending'"></span>
                            </div>

                            <!-- Order Body -->
                            <div class="p-5 flex-1 space-y-4">
                                <div>
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block">Pelanggan / Meja</span>
                                    <span class="text-base font-bold text-gray-800" x-text="order.customer_name"></span>
                                </div>

                                <div>
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block mb-2">Item Pesanan</span>
                                    <div class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                                        <template x-for="item in order.items">
                                            <div class="flex justify-between text-sm text-gray-600 border-b border-gray-50 pb-1">
                                                <span x-text="item.name"></span>
                                                <span class="font-bold text-amber-700" x-text="'x' + item.quantity"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Pembayaran</span>
                                    <span class="font-extrabold text-gray-900" x-text="'Rp. ' + Number(order.total_amount).toLocaleString('id-ID')"></span>
                                </div>
                            </div>

                            <!-- Order Actions -->
                            <div class="p-5 border-t border-gray-100 bg-gray-50 flex gap-2">
                                <!-- Button: Proses (Only show if pending) -->
                                <button x-show="order.status === 'pending'" @click="updateStatus(order.id, 'processing')" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Proses
                                </button>

                                <!-- Button: Selesai -->
                                <button @click="updateStatus(order.id, 'completed')" class="flex-1 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    Selesai
                                </button>

                                <!-- Button: Batalkan -->
                                <button @click="updateStatus(order.id, 'cancelled')" class="py-2 px-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 border border-red-200">
                                    Batalkan
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-if="activeOrders.length === 0">
                        <div class="col-span-full py-16 flex flex-col items-center justify-center text-gray-400 space-y-3 bg-white rounded-2xl border border-gray-200 p-8 shadow-sm">
                            <svg class="w-16 h-16 text-gray-200 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <p class="text-gray-500 font-medium">Tidak ada antrean pesanan aktif saat ini.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </main>

    <script>
        function posSystem() {
            return {
                products: @json($products),
                categories: @json($categories),
                activeCategory: 'all',
                cart: [],
                paymentMethod: 'cash',
                amountPaid: null,
                customerName: '',
                processing: false,
                currentTime: '',
                qrisModalOpen: false,
                qrisQrUrl: '',
                qrisAmount: 0,
                qrisOrderNumber: '',
                qrisCountdown: 900,
                qrisCountdownStr: '15:00',
                qrisStatus: 'pending',
                qrisInterval: null,
                qrisTimer: null,

                // Tab & active orders
                activeTab: 'pos',
                activeOrders: [],
                activeOrdersCount: 0,

                // Modal pilih qty + type
                productModal: false,
                selectedProduct: {},
                modalQty: 1,
                modalType: 'ice',

                init() {
                    window.addEventListener('resize', () => {
                        if (window.innerWidth >= 1024 && this.activeTab === 'cart') {
                            this.activeTab = 'pos';
                        }
                    });

                    setInterval(() => {
                        this.currentTime = new Date().toLocaleString('id-ID', { 
                            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                            hour: '2-digit', minute: '2-digit'
                        });
                    }, 1000);

                    const savedCart = localStorage.getItem('pos_cart');
                    if (savedCart) {
                        this.cart = JSON.parse(savedCart);
                        localStorage.removeItem('pos_cart');
                    }

                    this.fetchActiveOrders();
                    setInterval(() => this.fetchActiveOrders(), 5000);
                },

                get filteredProducts() {
                    if (this.activeCategory === 'all') return this.products;
                    return this.products.filter(p => p.category_id == this.activeCategory);
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                // Diskon 5% jika subtotal >= Rp 200.000
                get discount() {
                    return this.subtotal >= 200000 ? Math.round(this.subtotal * 0.05) : 0;
                },

                get tax() {
                    return Math.round((this.subtotal - this.discount) * 0.10);
                },

                get total() {
                    return this.subtotal - this.discount + this.tax;
                },

                get change() {
                    return this.amountPaid ? (this.amountPaid - this.total) : 0;
                },

                // Cek apakah produk termasuk kategori snack (makanan, tidak perlu ice/hot)
                isSnackCategory(product) {
                    if (!product || !product.category) return false;
                    const name = (product.category.name || '').toLowerCase();
                    const slug = (product.category.slug || '').toLowerCase();
                    return name.includes('snack') || slug.includes('snack');
                },

                // Buka modal pilih qty + type
                openProductModal(product) {
                    this.selectedProduct = product;
                    this.modalQty  = 1;
                    // Produk snack tidak memiliki pilihan ice/hot
                    this.modalType = this.isSnackCategory(product) ? 'none' : 'ice';
                    this.productModal = true;
                },

                // Tambah ke keranjang dari modal
                confirmAddToCart() {
                    const product = this.selectedProduct;
                    if (!product || this.modalQty < 1) return;

                    // Key unik per produk + jenis (snack pakai 'none')
                    const key = `${product.id}_${this.modalType}`;
                    const existing = this.cart.find(i => i.cartKey === key);

                    const totalQtyInCart = existing ? existing.quantity : 0;
                    if (totalQtyInCart + this.modalQty > product.stock) {
                        Swal.fire({
                            toast: true, position: 'top-end', showConfirmButton: false,
                            timer: 3000, icon: 'warning',
                            title: `Stok tidak cukup! Tersisa ${product.stock} item.`
                        });
                        return;
                    }

                    if (existing) {
                        existing.quantity += this.modalQty;
                    } else {
                        this.cart.push({
                            cartKey: key,
                            id:      product.id,
                            name:    product.name,
                            price:   product.price,
                            quantity: this.modalQty,
                            stock:   product.stock,
                            type:    this.modalType,
                        });
                    }

                    this.productModal = false;

                    // Notifikasi disesuaikan: snack tidak perlu menyebut ice/hot
                    const typeLabel = this.modalType === 'ice' ? ' (Ice)' : this.modalType === 'hot' ? ' (Hot)' : '';
                    Swal.fire({
                        toast: true, position: 'top-end', showConfirmButton: false,
                        timer: 1800, icon: 'success',
                        title: `${product.name}${typeLabel} ditambahkan!`
                    });
                },

                updateQuantity(index, delta) {
                    const item = this.cart[index];
                    const newQty = item.quantity + delta;
                    if (newQty > 0 && newQty <= item.stock) {
                        item.quantity = newQty;
                    } else if (newQty === 0) {
                        this.cart.splice(index, 1);
                    }
                },

                clearCart() {
                    Swal.fire({
                        title: 'Kosongkan Pesanan?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, kosongkan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.cart = [];
                            this.amountPaid = null;
                        }
                    })
                },

                async checkout() {
                    if (this.cart.length === 0) return;
                    if (this.paymentMethod === 'cash' && this.amountPaid < this.total) return;

                    this.processing = true;

                    try {
                        const response = await axios.post('{{ route("kasir.checkout") }}', {
                            items: this.cart,
                            payment_method: this.paymentMethod,
                            amount_paid: this.paymentMethod === 'cash' ? this.amountPaid : this.total,
                            customer_name: this.customerName,
                            discount: this.discount,
                        });

                        if (response.data.success) {
                            const orderNumber = response.data.order_number;
                            
                            if (response.data.payment_method === 'cash') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Transaksi Tunai Sukses!',
                                    html: `
                                        <div class="text-left mt-4 bg-gray-50 p-4 rounded-lg text-sm">
                                            <p class="font-bold text-gray-800 border-b pb-2 mb-2">No. Order: ${orderNumber}</p>
                                            <p class="flex justify-between"><span>Total:</span> <span class="font-bold">Rp. ${Number(this.total).toLocaleString('id-ID')}</span></p>
                                            <p class="flex justify-between"><span>Dibayar:</span> <span>Rp. ${Number(this.amountPaid).toLocaleString('id-ID')}</span></p>
                                            <p class="flex justify-between text-green-600 font-bold border-t pt-2 mt-2"><span>Kembali:</span> <span>Rp. ${Number(response.data.change).toLocaleString('id-ID')}</span></p>
                                        </div>
                                    `,
                                    showCancelButton: true,
                                    confirmButtonText: 'Cetak Struk',
                                    cancelButtonText: 'Tutup & Transaksi Baru',
                                    confirmButtonColor: '#D97706',
                                    cancelButtonColor: '#6B7280',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.open('/kasir/orders/' + orderNumber + '/receipt', 'Cetak Struk', 'width=400,height=600');
                                    }
                                    window.location.reload();
                                });
                            } else if (response.data.payment_method === 'qris') {
                                this.qrisQrUrl = response.data.qr_url;
                                this.qrisAmount = response.data.total;
                                this.qrisOrderNumber = response.data.order_number;
                                this.qrisStatus = 'pending';
                                this.qrisCountdown = 900;
                                this.qrisCountdownStr = '15:00';
                                this.qrisModalOpen = true;

                                if (this.qrisTimer) clearInterval(this.qrisTimer);
                                this.qrisTimer = setInterval(() => {
                                    if (this.qrisCountdown > 0) {
                                        this.qrisCountdown--;
                                        const mins = Math.floor(this.qrisCountdown / 60);
                                        const secs = this.qrisCountdown % 60;
                                        this.qrisCountdownStr = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                                    } else {
                                        this.qrisStatus = 'expired';
                                        clearInterval(this.qrisTimer);
                                        clearInterval(this.qrisInterval);
                                    }
                                }, 1000);

                                if (this.qrisInterval) clearInterval(this.qrisInterval);
                                this.qrisInterval = setInterval(async () => {
                                    try {
                                        const statusRes = await axios.get(`/orders/${this.qrisOrderNumber}/status`);
                                        if (statusRes.data && statusRes.data.status !== 'pending') {
                                            this.qrisStatus = statusRes.data.status === 'completed' ? 'success' : 'expired';
                                            
                                            clearInterval(this.qrisInterval);
                                            clearInterval(this.qrisTimer);

                                            if (this.qrisStatus === 'success') {
                                                try {
                                                    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2013/2013-84.wav');
                                                    audio.volume = 0.5;
                                                    audio.play();
                                                } catch (e) {}
                                                // Tombol Cetak Struk & Transaksi Baru ada langsung di modal
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Pembayaran Expired / Gagal',
                                                    text: 'Waktu pembayaran habis atau dibatalkan.'
                                                }).then(() => {
                                                    window.location.reload();
                                                });
                                            }
                                        }
                                    } catch (err) {
                                        console.error("Gagal check status:", err);
                                    }
                                }, 2000);
                            } else {
                                // Midtrans Snap payment
                                const snapToken = response.data.snap_token;
                                window.snap.pay(snapToken, {
                                    onSuccess: function(result) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Pembayaran Sukses!',
                                            text: 'Transaksi digital berhasil diverifikasi oleh Midtrans.',
                                            showCancelButton: true,
                                            confirmButtonText: 'Cetak Struk',
                                            cancelButtonText: 'Transaksi Baru',
                                            confirmButtonColor: '#D97706',
                                            allowOutsideClick: false
                                        }).then((res) => {
                                            if (res.isConfirmed) {
                                                window.open('/kasir/orders/' + orderNumber + '/receipt', 'Cetak Struk', 'width=400,height=600');
                                            }
                                            window.location.reload();
                                        });
                                    },
                                    onPending: function(result) {
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Pembayaran Pending',
                                            text: 'Silakan pelanggan melakukan scan QRIS / menyelesaikan pembayaran di HP.',
                                            confirmButtonText: 'Tutup (Tunggu Callback)',
                                            confirmButtonColor: '#D97706',
                                            allowOutsideClick: false
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    },
                                    onError: function(result) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Pembayaran Gagal',
                                            text: 'Terjadi kesalahan saat memproses pembayaran di Midtrans.'
                                        });
                                        window.location.reload();
                                    },
                                    onClose: function() {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Pembayaran Tertunda',
                                            text: 'Transaksi tersimpan sebagai pending. Transaksi dapat diselesaikan di lain waktu.'
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    }
                                });
                            }
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Transaksi Gagal',
                            text: error.response?.data?.message || 'Terjadi kesalahan sistem'
                        });
                        this.processing = false;
                    }
                },
                async simulateQrisSuccess() {
                    try {
                        const response = await axios.post(`/orders/${this.qrisOrderNumber}/simulate-success`);
                        if (response.data.success) {
                            this.qrisStatus = 'success';
                            clearInterval(this.qrisInterval);
                            clearInterval(this.qrisTimer);

                            try {
                                const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2013/2013-84.wav');
                                audio.volume = 0.5;
                                audio.play();
                            } catch (e) {}
                            // Tombol Cetak Struk & Transaksi Baru sudah ada langsung di modal
                        }
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Simulasi Gagal',
                            text: err.response?.data?.message || 'Gagal mengirim request simulasi.'
                        });
                    }
                },
                async fetchActiveOrders() {
                    try {
                        const response = await axios.get('/kasir/orders/active');
                        this.activeOrders = response.data;
                        this.activeOrdersCount = this.activeOrders.length;
                    } catch (error) {
                        console.error('Gagal mengambil daftar antrean:', error);
                    }
                },
                async updateStatus(id, newStatus) {
                    let confirmTitle = 'Konfirmasi Pesanan';
                    let confirmText = `Ubah status pesanan ke: ${newStatus === 'processing' ? 'Diproses' : (newStatus === 'completed' ? 'Selesai' : 'Batal')}?`;
                    let confirmButtonColor = '#3B82F6'; // Blue

                    if (newStatus === 'completed') {
                        confirmButtonColor = '#10B981'; // Green
                    } else if (newStatus === 'cancelled') {
                        confirmTitle = 'Batalkan Pesanan?';
                        confirmText = 'Apakah Anda yakin ingin membatalkan pesanan ini? Stok produk akan dikembalikan.';
                        confirmButtonColor = '#EF4444'; // Red
                    }

                    const swalResult = await Swal.fire({
                        title: confirmTitle,
                        text: confirmText,
                        icon: newStatus === 'cancelled' ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: confirmButtonColor,
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Ya, Lanjutkan!',
                        cancelButtonText: 'Batal'
                    });

                    if (!swalResult.isConfirmed) return;

                    try {
                        // Pastikan CSRF token selalu fresh sebelum request
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const response = await axios.patch(`/kasir/orders/${id}/status`, {
                            status: newStatus
                        }, {
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            }
                        });

                        if (response.data.success) {
                            const label = newStatus === 'cancelled' ? 'dibatalkan' : (newStatus === 'completed' ? 'selesai' : 'diproses');
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: newStatus === 'cancelled' ? 'info' : 'success',
                                title: response.data.message || `Pesanan berhasil ${label}.`,
                                showConfirmButton: false,
                                timer: 3000
                            });
                            this.fetchActiveOrders();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Memperbarui Status',
                                text: response.data.message || 'Server menolak permintaan ini.'
                            });
                        }
                    } catch (error) {
                        let errMsg = 'Terjadi kesalahan sistem.';
                        if (error.response) {
                            if (error.response.status === 419) {
                                errMsg = 'Sesi telah berakhir (CSRF Token tidak valid). Silakan refresh halaman dan coba lagi.';
                            } else if (error.response.status === 403) {
                                errMsg = 'Anda tidak memiliki izin untuk melakukan aksi ini.';
                            } else if (error.response.status === 404) {
                                errMsg = 'Pesanan tidak ditemukan.';
                            } else {
                                errMsg = error.response?.data?.message || errMsg;
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memperbarui Status',
                            text: errMsg
                        });
                    }
                }
            }
        }
    </script>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- Modal: Pilih Jumlah & Jenis Minuman                                -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <template x-if="productModal">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         @keydown.escape.window="productModal = false"
         @click.self="productModal = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden"
             @click.stop>

            <!-- Header: Foto + Info Produk -->
            <div class="relative">
                <template x-if="selectedProduct.image">
                    <img :src="'/Images/' + selectedProduct.image"
                         class="w-full h-44 object-cover" alt="">
                </template>
                
                <template x-if="!selectedProduct.image">
                    <div class="w-full h-44 bg-amber-50 flex items-center justify-center text-amber-300">
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </template>

                <!-- Tombol tutup -->
                <button @click="productModal = false"
                        class="absolute top-3 right-3 w-8 h-8 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center text-gray-500 hover:text-gray-800 shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-5">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" x-text="selectedProduct.name"></h3>
                    <p class="text-amber-600 font-semibold text-sm mt-0.5"
                       x-text="'Rp. ' + Number(selectedProduct.price).toLocaleString('id-ID')"></p>
                    <p class="text-xs text-gray-400 mt-0.5"
                       x-text="'Stok tersedia: ' + selectedProduct.stock"></p>
                </div>

                <!-- Pilihan Jenis: Ice / Hot (hanya tampil untuk produk minuman, bukan snack) -->
                <div x-show="!isSnackCategory(selectedProduct)">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jenis Minuman</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button @click="modalType = 'ice'"
                                :class="modalType === 'ice'
                                    ? 'border-sky-400 bg-sky-50 text-sky-700 shadow-sm'
                                    : 'border-gray-200 bg-white text-gray-500 hover:border-sky-200'"
                                class="flex flex-col items-center justify-center py-3 rounded-xl border-2 font-semibold text-sm transition-all">
                            <span class="text-2xl mb-1">🧊</span>
                            Ice
                        </button>
                        <button @click="modalType = 'hot'"
                                :class="modalType === 'hot'
                                    ? 'border-orange-400 bg-orange-50 text-orange-700 shadow-sm'
                                    : 'border-gray-200 bg-white text-gray-500 hover:border-orange-200'"
                                class="flex flex-col items-center justify-center py-3 rounded-xl border-2 font-semibold text-sm transition-all">
                            <span class="text-2xl mb-1">☕</span>
                            Hot
                        </button>
                    </div>
                </div>

                <!-- Stepper Jumlah -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Jumlah</label>
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl p-1">
                        <button @click="modalQty = Math.max(1, modalQty - 1)"
                                class="w-11 h-11 flex items-center justify-center rounded-lg text-gray-600 hover:bg-gray-200 transition text-xl font-bold">
                            −
                        </button>
                        <span class="text-2xl font-black text-gray-900 w-10 text-center" x-text="modalQty"></span>
                        <button @click="modalQty < selectedProduct.stock ? modalQty++ : null"
                                :class="modalQty >= selectedProduct.stock ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-200'"
                                class="w-11 h-11 flex items-center justify-center rounded-lg text-gray-600 transition text-xl font-bold">
                            +
                        </button>
                    </div>
                    <!-- Subtotal item -->
                    <p class="text-right text-sm font-semibold text-amber-600 mt-2"
                       x-text="'Subtotal: Rp. ' + Number(selectedProduct.price * modalQty).toLocaleString('id-ID')"></p>
                </div>

                <!-- Tombol Aksi -->
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <button @click="productModal = false"
                            class="py-3 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button @click="confirmAddToCart()"
                            class="py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm transition shadow-md flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Tambah
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>

    <!-- QRIS POS Realtime Modal -->
    <div x-show="qrisModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div class="bg-white border border-gray-200 w-full max-w-md p-6 sm:p-8 rounded-2xl shadow-2xl relative space-y-6 text-center" @click.away="qrisModalOpen = false; clearInterval(qrisInterval); clearInterval(qrisTimer);">
            <button @click="qrisModalOpen = false; clearInterval(qrisInterval); clearInterval(qrisTimer);" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- Invoice details -->
            <div class="space-y-1">
                <h3 class="text-xs uppercase tracking-widest text-gray-500 font-bold">QRIS Pembayaran Kasir</h3>
                <p class="text-sm font-semibold text-amber-600" x-text="qrisOrderNumber"></p>
                <p class="text-xs text-gray-500" x-show="customerName">Pelanggan: <span class="font-bold text-gray-700" x-text="customerName"></span></p>
                <div class="text-3xl font-black text-gray-900 mt-2 whitespace-nowrap" x-text="'Rp. ' + Number(qrisAmount).toLocaleString('id-ID')"></div>
            </div>

            <!-- QR code image container with rounded border -->
            <div class="flex justify-center items-center bg-white p-4 rounded-xl border border-gray-200 w-60 h-60 mx-auto relative shadow-inner">
                <img :src="qrisQrUrl" alt="QRIS Code" class="w-full h-full object-contain" />
                
                <!-- Overlay Success or Expired state -->
                <div x-show="qrisStatus === 'success'" class="absolute inset-0 bg-white/95 rounded-xl flex flex-col justify-center items-center space-y-2">
                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white shadow-[0_0_20px_rgba(34,197,94,0.4)] animate-pulse">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-green-600 font-bold text-sm">Pembayaran Lunas!</p>
                    <p class="text-xs text-gray-400" x-text="qrisOrderNumber"></p>
                </div>

                <div x-show="qrisStatus === 'expired'" class="absolute inset-0 bg-white/95 rounded-xl flex flex-col justify-center items-center space-y-2">
                    <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center text-white">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <p class="text-red-600 font-bold text-sm">QRIS Expired</p>
                </div>
            </div>

            <!-- Status Indicator and Loading Checking Payment -->
            <div class="space-y-4">
                <!-- Status text -->
                <div class="flex items-center justify-center gap-3">
                    <div class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                              :class="qrisStatus === 'pending' ? 'bg-amber-500' : (qrisStatus === 'success' ? 'bg-green-500' : 'bg-red-500')"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3"
                              :class="qrisStatus === 'pending' ? 'bg-amber-500' : (qrisStatus === 'success' ? 'bg-green-500' : 'bg-red-500')"></span>
                    </div>
                    <span class="text-sm font-bold text-gray-700"
                          x-text="qrisStatus === 'pending' ? 'Menunggu Pelanggan Scan...' : (qrisStatus === 'success' ? 'Pembayaran Sukses' : 'Kedaluwarsa')"></span>
                </div>

                <!-- Countdown Timer (only shown when pending) -->
                <div x-show="qrisStatus === 'pending'" class="text-xs text-gray-500">
                    Selesaikan dalam <span class="font-extrabold text-amber-600 text-sm" x-text="qrisCountdownStr">15:00</span>
                </div>

                <!-- Action Buttons saat QRIS Sukses -->
                <div x-show="qrisStatus === 'success'" class="flex flex-col gap-2 pt-1">
                    <button @click="window.open('/kasir/orders/' + qrisOrderNumber + '/receipt', 'Cetak Struk', 'width=420,height=650'); window.location.reload();" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm flex items-center justify-center gap-2 transition shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Struk
                    </button>
                    <button @click="window.location.reload()" class="w-full py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl text-sm transition">
                        Transaksi Baru
                    </button>
                </div>

                <!-- Action Button saat QRIS Expired -->
                <div x-show="qrisStatus === 'expired'" class="pt-1">
                    <button @click="window.location.reload()" class="w-full py-2.5 px-4 bg-gray-700 hover:bg-gray-800 text-white font-bold rounded-xl text-sm transition">
                        Batal & Transaksi Baru
                    </button>
                </div>

                @if(config('app.env') === 'local')
                <div x-show="qrisStatus === 'pending'" class="pt-1">
                    <button @click="simulateQrisSuccess()" class="text-xs bg-amber-500 hover:bg-amber-600 text-white font-bold py-1.5 px-4 rounded-full transition shadow-sm">
                        Simulasi Bayar Sukses
                    </button>
                </div>
                @endif

                <div class="flex justify-center items-center gap-1.5 text-xs text-gray-400 border-t border-gray-100 pt-4">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg" alt="QRIS" class="h-6 object-contain opacity-70" />
                    <span>Dapat discan dengan OVO, GoPay, ShopeePay, Dana, M-Banking</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
