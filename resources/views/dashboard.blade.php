<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-amber-500 leading-tight uppercase tracking-wider">
            {{ __('Dashboard Pelanggan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#120E0C] min-h-screen text-gray-200">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-6 py-4 rounded-2xl relative shadow-lg" role="alert">
                    <span class="block sm:inline font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 px-6 py-4 rounded-2xl relative shadow-lg" role="alert">
                    <span class="block sm:inline font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-3xl p-6 sm:p-8 shadow-2xl">
                <div class="text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-black text-white uppercase tracking-wider">Riwayat Pesanan Anda</h3>
                        <a href="{{ route('home') }}" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-full font-bold text-sm shadow-[0_0_15px_rgba(217,119,6,0.3)] transition-all">
                            Pesan Kopi Baru
                        </a>
                    </div>
                    
                    @if($orders->isEmpty())
                        <div class="text-center py-12 border border-dashed border-white/10 rounded-2xl bg-white/[0.02]">
                            <p class="text-gray-400 mb-4">Anda belum memiliki riwayat pesanan.</p>
                            <a href="{{ route('home') }}" class="text-amber-500 font-bold hover:text-amber-400">Mulai Belanja &rarr;</a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($orders as $order)
                                <div class="bg-white/[0.03] border border-white/5 hover:border-amber-500/30 rounded-2xl p-5 flex flex-col justify-between transition-all duration-300 shadow-md">
                                    <div>
                                        <div class="flex justify-between items-start border-b border-white/10 pb-4 mb-4">
                                            <div>
                                                <p class="font-extrabold text-amber-500 tracking-wide text-lg">{{ $order->order_number }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->format('d M Y H:i') }} WIB</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="px-3 py-1 rounded-full text-xs font-black tracking-wide uppercase inline-block
                                                    {{ $order->status == 'completed' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : ($order->status == 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20') }}">
                                                    {{ $order->status }}
                                                </span>
                                                <p class="font-black text-white mt-2 text-lg">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 mb-6">
                                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Detail Item:</p>
                                            <ul class="text-sm text-gray-300 divide-y divide-white/5">
                                                @foreach($order->items as $item)
                                                    <li class="py-2 flex justify-between">
                                                        <span>{{ $item->product->name }} <span class="text-amber-500 font-bold">x{{ $item->quantity }}</span></span>
                                                        <span class="text-gray-400">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="pt-4 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-4">
                                        <a href="{{ route('orders.invoice', $order->order_number) }}" class="w-full sm:w-auto px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-full text-xs font-bold text-center border border-white/10 transition-all flex items-center justify-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            Lihat Invoice Digital
                                        </a>

                                        @if($order->status == 'completed' && !\App\Models\Review::where('order_id', $order->id)->exists())
                                            <div class="w-full sm:w-auto" x-data="{ openReview: false }">
                                                <button @click="openReview = !openReview" class="w-full text-amber-500 text-xs font-bold hover:text-amber-400 transition-all text-center flex items-center justify-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.36 1.246.58 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.178 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.05 10.1c-.778-.564-.38-1.81.58-1.81h4.908a1 1 0 00.95-.69l1.519-4.674z"></path></svg>
                                                    Beri Ulasan
                                                </button>
                                                
                                                <!-- Review Modal/Drawer -->
                                                <div x-show="openReview" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.away="openReview = false" x-cloak>
                                                    <div class="bg-[#1C1513] border border-white/10 w-full max-w-md p-6 rounded-3xl shadow-2xl relative">
                                                        <button @click="openReview = false" class="absolute top-4 right-4 text-gray-400 hover:text-white">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                        
                                                        <h4 class="text-lg font-black text-white uppercase tracking-wider mb-4 border-b border-white/10 pb-2">Beri Ulasan</h4>
                                                        
                                                        <form action="{{ route('reviews.store') }}" method="POST" class="space-y-4">
                                                            @csrf
                                                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                            <div>
                                                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Rating</label>
                                                                <select name="rating" class="w-full bg-[#120E0C] border border-white/10 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:ring-1 focus:ring-amber-500 text-sm">
                                                                    <option value="5">⭐⭐⭐⭐⭐ Sangat Bagus</option>
                                                                    <option value="4">⭐⭐⭐⭐ Bagus</option>
                                                                    <option value="3">⭐⭐⭐ Lumayan</option>
                                                                    <option value="2">⭐⭐ Kurang</option>
                                                                    <option value="1">⭐ Sangat Kurang</option>
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Ulasan</label>
                                                                <textarea name="comment" rows="3" class="w-full bg-[#120E0C] border border-white/10 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:ring-1 focus:ring-amber-500 text-sm" placeholder="Bagaimana rasa kopi dan layanannya?"></textarea>
                                                            </div>
                                                            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-full font-bold text-sm transition-all shadow-[0_0_15px_rgba(217,119,6,0.3)]">
                                                                Kirim Ulasan
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif(\App\Models\Review::where('order_id', $order->id)->exists())
                                            <span class="text-xs text-green-400 font-bold flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Sudah Diulas
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
