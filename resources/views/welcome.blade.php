<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZCoffe Hening</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #140E0C; }
        .font-serif { font-family: 'Playfair Display', serif; }
        
        /* Premium Glassmorphism */
        .glass-panel { 
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }
        
        .glass-card {
            background: linear-gradient(145deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .glass-card:hover {
            transform: translateY(-10px);
            border-color: rgba(212, 175, 55, 0.3);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 20px rgba(212, 175, 55, 0.1);
        }

        .gold-text-gradient { 
            background: linear-gradient(135deg, #FDE047 0%, #D4AF37 50%, #AA7C11 100%); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            background-clip: text;
        }

        /* Custom Scrollbar and hide scrollbar utility */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #140E0C; }
        ::-webkit-scrollbar-thumb { background: #3D2B24; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #D4AF37; }

        /* Animation Classes applied via JS Observer */
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s ease-out; }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        
        /* Subtle noise texture overlay */
        .noise-overlay {
            position: fixed; inset: 0; z-index: 9999; pointer-events: none; opacity: 0.03;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="text-gray-200 antialiased selection:bg-gold selection:text-coffee-900" x-data="storeApp()">
    <div class="noise-overlay"></div>

    <!-- Dynamic Navbar -->
    <nav class="fixed w-full z-50 transition-all duration-500" :class="scrolled ? 'glass-panel py-3' : 'bg-transparent py-6'" @scroll.window="scrolled = (window.pageYOffset > 50)">
        <div class="container mx-auto px-6 lg:px-12 flex items-center justify-between">
            <a href="#" class="text-2xl md:text-3xl font-extrabold tracking-widest text-white flex items-center gap-2 group">
                <div class="w-8 h-8 rounded-full bg-gold flex items-center justify-center text-coffee-900 group-hover:rotate-180 transition-transform duration-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                Z<span class="text-gold">COFFEE</span>
            </a>
            
            <div class="hidden md:flex items-center gap-10 text-sm font-semibold tracking-wide">
                <a href="#home" class="text-gray-300 hover:text-gold transition-colors relative after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-gold hover:after:w-full after:transition-all after:duration-300">Beranda</a>
                <a href="#menu" class="text-gray-300 hover:text-gold transition-colors relative after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-gold hover:after:w-full after:transition-all after:duration-300">Menu Premium</a>
                <a href="#about" class="text-gray-300 hover:text-gold transition-colors relative after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-gold hover:after:w-full after:transition-all after:duration-300">Cerita Kami</a>
                <a href="#reviews" class="text-gray-300 hover:text-gold transition-colors relative after:absolute after:-bottom-1 after:left-0 after:w-0 after:h-0.5 after:bg-gold hover:after:w-full after:transition-all after:duration-300">Testimoni</a>
            </div>

            <div class="flex items-center gap-5">
                <!-- Cart Trigger with pulse -->
                <button @click="cartOpen = true" class="relative p-2 text-gray-300 hover:text-gold transition-all transform hover:scale-110 group">
                    <div class="absolute inset-0 bg-gold/20 rounded-full scale-0 group-hover:scale-150 opacity-0 group-hover:opacity-100 transition-all duration-500"></div>
                    <svg class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span x-show="cart.length > 0" x-transition.scale x-text="cartTotalItems" class="absolute -top-1 -right-1 w-5 h-5 bg-gold text-coffee-900 text-[11px] font-extrabold flex items-center justify-center rounded-full shadow-[0_0_10px_rgba(212,175,55,0.6)]"></span>
                </button>

                @auth
                    @if(in_array(auth()->user()->role, ['admin', 'kasir']))
                        <div class="hidden md:flex items-center gap-3 border-l border-white/10 pl-5">
                            <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('kasir.index') }}" class="text-sm font-semibold text-white hover:text-gold transition">Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 text-sm font-bold text-coffee-900 bg-gold hover:bg-gold-light rounded-full shadow-[0_0_15px_rgba(212,175,55,0.3)] transition-all">Keluar</button>
                            </form>
                        </div>
                    @else
                        <div class="hidden md:flex items-center gap-3 border-l border-white/10 pl-5">
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 text-sm font-bold text-coffee-900 bg-gold hover:bg-gold-light rounded-full shadow-[0_0_15px_rgba(212,175,55,0.3)] transition-all">Keluar</button>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="hidden md:flex items-center gap-3 border-l border-white/10 pl-5">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-white hover:text-gold transition">Masuk</a>
                    </div>
                @endauth

                <!-- Hamburger Menu Button (Mobile) -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden ml-2 p-2 text-gray-300 hover:text-gold transition-colors focus:outline-none">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200" 
             x-transition:enter-start="opacity-0 -translate-y-4" 
             x-transition:enter-end="opacity-100 translate-y-0" 
             x-transition:leave="transition ease-in duration-150" 
             x-transition:leave-start="opacity-100 translate-y-0" 
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden absolute top-full left-0 w-full glass-panel border-t border-white/10 shadow-2xl py-4 px-6 flex flex-col gap-4 bg-coffee-900/95" x-cloak>
            
            <a href="#home" @click="mobileMenuOpen = false" class="text-white hover:text-gold py-2 border-b border-white/5 font-semibold">Beranda</a>
            <a href="#menu" @click="mobileMenuOpen = false" class="text-white hover:text-gold py-2 border-b border-white/5 font-semibold">Menu Premium</a>
            <a href="#about" @click="mobileMenuOpen = false" class="text-white hover:text-gold py-2 border-b border-white/5 font-semibold">Cerita Kami</a>
            <a href="#reviews" @click="mobileMenuOpen = false" class="text-white hover:text-gold py-2 border-b border-white/5 font-semibold">Testimoni</a>

            <div class="pt-2">
                @auth
                    @if(in_array(auth()->user()->role, ['admin', 'kasir']))
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('kasir.index') }}" class="block w-full text-center py-3 mb-3 text-sm font-bold text-white border border-white/20 rounded-full hover:bg-white/5 transition-all">Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full text-center py-3 text-sm font-bold text-coffee-900 bg-gold hover:bg-gold-light rounded-full shadow-lg transition-all">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center py-3 text-sm font-bold text-coffee-900 bg-gold hover:bg-gold-light rounded-full shadow-lg transition-all">Masuk</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Premium Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        <!-- Abstract Background -->
        <div class="absolute inset-0 bg-coffee-900 z-0">
            <!-- Glowing orbs -->
            <div class="absolute top-1/4 -left-20 w-96 h-96 bg-gold/10 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-amber-900/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="container mx-auto px-6 lg:px-12 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="text-left animate-slide-up">
                <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-[1.1] text-white">
                    Seni <span class="font-serif italic font-normal text-gray-400">dalam</span><br>
                    <span class="gold-text-gradient">Secangkir Kopi.</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-400 mb-10 max-w-xl font-light leading-relaxed">
                    Rasakan pengalaman minum kopi yang tidak hanya membangkitkan semangat, tapi juga memanjakan indera. Diseduh sempurna dari biji pilihan Nusantara.
                </p>
                <div class="flex flex-wrap gap-5">
                    <a href="#menu" class="px-8 py-4 bg-gold hover:bg-gold-light text-coffee-900 font-extrabold rounded-full shadow-[0_0_20px_rgba(212,175,55,0.4)] hover:shadow-[0_0_30px_rgba(212,175,55,0.6)] transition-all transform hover:-translate-y-1 flex items-center gap-2 group">
                        Pesan Sekarang
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
            
            <div class="relative hidden lg:block animate-float">
                <!-- Decorative Elements -->
                <div class="absolute -top-10 -right-10 w-40 h-40 border border-gold/20 rounded-full"></div>
                <div class="absolute bottom-10 -left-10 w-24 h-24 border border-gold/30 rounded-full"></div>
                
                <div class="relative w-[500px] h-[600px] mx-auto">
                    <img src="https://images.unsplash.com/photo-1559525839-b184a4d698c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Premium Coffee" class="absolute inset-0 w-full h-full object-cover rounded-[40px] shadow-2xl z-10" style="border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;">
                    
                    <!-- Floating Badge -->
                    <div class="absolute top-20 -right-8 glass-panel px-6 py-4 rounded-2xl z-20 animate-float-delayed flex items-center gap-4">
                        <div class="text-gold">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                        <div>
                            <p class="text-white font-bold text-lg leading-none">4.9/5</p>
                            <p class="text-gray-400 text-xs mt-1">Rating Pelanggan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Section (The Ordering Experience) -->
    <section id="menu" class="py-32 relative bg-coffee-800">
        <!-- Top border gradient -->
        <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-gold/50 to-transparent"></div>
        
        <div class="container mx-auto px-6 lg:px-12">
            <div class="text-center mb-20 reveal">
                <p class="text-gold font-bold tracking-widest uppercase mb-3 text-sm">Pilihan Terbaik Kami</p>
                <h2 class="text-4xl md:text-6xl font-extrabold text-white mb-6">Menu <span class="font-serif italic text-gold">Premium</span></h2>
                <p class="text-gray-400 max-w-2xl mx-auto">Setiap cangkir disajikan dengan presisi untuk menghasilkan harmoni rasa yang sempurna. Silakan pilih kebahagiaan Anda hari ini.</p>
            </div>

            <!-- Sleek Categories -->
            <div class="flex justify-center gap-4 mb-16 flex-wrap reveal delay-100">
                <button @click="activeCategory = 'all'" 
                        :class="activeCategory === 'all' ? 'bg-gold text-coffee-900 shadow-[0_0_20px_rgba(212,175,55,0.4)]' : 'bg-coffee-900/50 text-gray-400 border border-white/5 hover:border-white/20 hover:text-white'" 
                        class="px-8 py-3 rounded-full font-bold transition-all duration-300">
                    Semua
                </button>
                @foreach($categories as $cat)
                    <button @click="activeCategory = '{{ $cat->id }}'" 
                            :class="activeCategory === '{{ $cat->id }}' ? 'bg-gold text-coffee-900 shadow-[0_0_20px_rgba(212,175,55,0.4)]' : 'bg-coffee-900/50 text-gray-400 border border-white/5 hover:border-white/20 hover:text-white'" 
                            class="px-8 py-3 rounded-full font-bold transition-all duration-300">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <!-- Premium Product Grid with Horizontal Scroll -->
            <div class="relative group reveal delay-200" x-data="{ 
                scrollNext() { this.$refs.scrollContainer.scrollBy({ left: 400, behavior: 'smooth' }) },
                scrollPrev() { this.$refs.scrollContainer.scrollBy({ left: -400, behavior: 'smooth' }) }
            }">
                <!-- Nav Buttons (Desktop Only) -->
                <button @click="scrollPrev" class="absolute -left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full glass-panel flex items-center justify-center text-gold z-30 opacity-0 group-hover:opacity-100 transition-opacity hidden md:flex border border-gold/30 hover:bg-gold/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button @click="scrollNext" class="absolute -right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full glass-panel flex items-center justify-center text-gold z-30 opacity-0 group-hover:opacity-100 transition-opacity hidden md:flex border border-gold/30 hover:bg-gold/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <div x-ref="scrollContainer" class="flex justify-start overflow-x-auto pb-8 gap-6 no-scrollbar scroll-smooth snap-x touch-pan-x">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="glass-card rounded-[24px] p-4 group flex flex-col flex-shrink-0 flex-grow-0 relative overflow-hidden snap-start" style="width: 260px; min-width: 260px;">
                        
                        <!-- Glowing backdrop for card -->
                        <div class="absolute -inset-2 bg-gradient-to-br from-gold/0 to-gold/0 group-hover:from-gold/5 group-hover:to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-[30px] pointer-events-none"></div>

                        <!-- Product Image Box -->
                        <div class="w-full aspect-square rounded-[20px] bg-coffee-900 mb-4 overflow-hidden relative border border-white/5 shadow-inner">
                            <template x-if="product.image">
                                <img :src="'{{ asset('Images') }}/' + product.image" class="w-full h-full object-cover group-hover:scale-110 transition duration-700 ease-out">
                            </template>
                            <template x-if="!product.image">
                                <div class="absolute inset-0 flex items-center justify-center text-gray-700 group-hover:scale-110 transition duration-700">
                                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                            </template>
                            
                            <!-- Badges -->
                            <template x-if="product.stock <= 10 && product.stock > 0">
                                <div class="absolute top-4 left-4 glass-panel text-white text-xs font-bold px-4 py-1.5 rounded-full backdrop-blur-md border border-white/20">Sisa <span x-text="product.stock"></span></div>
                            </template>
                            <template x-if="product.stock === 0">
                                <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] flex items-center justify-center z-10">
                                    <span class="bg-red-600/90 text-white font-extrabold tracking-widest px-6 py-2 rounded-full transform -rotate-12 border border-red-400/50 shadow-[0_0_20px_rgba(220,38,38,0.5)] uppercase text-xs">Habis Terjual</span>
                                </div>
                            </template>

                            <!-- Hover Add to Cart Overlay (Desktop) -->
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-all duration-300 hidden md:flex items-center justify-center z-10" x-show="product.stock > 0">
                                <button @click="addToCart(product)" class="translate-y-4 group-hover:translate-y-0 transition-all duration-500 bg-gold hover:bg-gold-light text-coffee-900 font-bold px-8 py-3 rounded-full flex items-center gap-2 shadow-[0_0_20px_rgba(212,175,55,0.5)] active:scale-95">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah
                                </button>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="px-2 pb-2 flex-grow flex flex-col">
                            <h3 class="text-lg font-bold text-white mb-1 group-hover:text-gold transition-colors line-clamp-1" x-text="product.name"></h3>
                            <p class="text-xs text-gray-500 mb-4 flex-grow line-clamp-2" x-text="product.description"></p>
                            
                            <div class="flex items-end justify-between mt-auto">
                                <div>
                                    <p class="text-[9px] text-gray-500 uppercase tracking-tighter">Harga</p>
                                    <span class="text-lg font-black text-white" x-text="'Rp ' + Math.round(product.price).toLocaleString('id-ID')"></span>
                                </div>
                                
                                <!-- Mobile Add to Cart -->
                                <button @click="addToCart(product)" :disabled="product.stock === 0" :class="product.stock === 0 ? 'hidden' : 'md:hidden bg-gold text-coffee-900'" class="p-4 rounded-full shadow-[0_0_15px_rgba(212,175,55,0.3)] active:scale-95 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- Scroll Indicators -->
            <div class="flex justify-center gap-2 mt-4">
                <div class="w-8 h-1 bg-gold rounded-full"></div>
                <div class="w-2 h-1 bg-white/10 rounded-full"></div>
                <div class="w-2 h-1 bg-white/10 rounded-full"></div>
            </div>
        </div>
    </section>

    <!-- Aesthetic About Section (Cerita Kami) -->
    <section id="about" class="py-32 bg-coffee-900 relative overflow-hidden">
        <div class="absolute -right-20 top-1/2 w-64 h-64 bg-gold/5 rounded-full blur-3xl"></div>
        <div class="container mx-auto px-6 lg:px-12">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <div class="lg:w-1/2 reveal">
                    <div class="relative">
                        <div class="absolute -inset-4 border border-gold/20 rounded-[40px] translate-x-4 translate-y-4"></div>
                        <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Coffee Story" class="relative z-10 w-full rounded-[40px] shadow-2xl object-cover h-[500px]">
                        <div class="absolute -bottom-10 -right-10 glass-panel p-8 rounded-3xl z-20 hidden md:block">
                            <p class="font-serif italic text-gold text-2xl">"Every bean has a story to tell."</p>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2 reveal delay-200">
                    <p class="text-gold font-bold tracking-widest uppercase mb-4 text-sm">Cerita Kami</p>
                    <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-8 leading-tight">Membawa <span class="font-serif italic text-gold">Seni</span> ke Dalam Setiap Seduhan</h2>
                    <div class="space-y-6 text-gray-400 text-lg font-light leading-relaxed">
                        <p>Berawal dari sebuah garasi kecil di tahun 2021, <span class="text-white font-semibold">ZCoffee Hening</span> lahir with satu misi sederhana: menjadikan kopi berkualitas dapat dinikmati oleh semua orang dengan cara yang artistik.</p>
                        <p>Kami tidak hanya menyajikan minuman, kami menyajikan <span class="text-gold italic">pengalaman</span>. Menggunakan biji kopi pilihan dari petani lokal Nusantara, setiap gelas yang sampai ke tangan Anda adalah hasil dari dedikasi dan cinta kami terhadap seni perkopian.</p>
                    </div>
                    <div class="mt-12 flex gap-8">
                        <div>
                            <p class="text-3xl font-black text-white">5K+</p>
                            <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Cangkir Terjual</p>
                        </div>
                        <div class="w-[1px] h-12 bg-white/10"></div>
                        <div>
                            <p class="text-3xl font-black text-white">20+</p>
                            <p class="text-xs text-gray-500 uppercase tracking-widest mt-1">Varian Menu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Shopping Cart Drawer -->
    <div x-show="cartOpen" class="fixed inset-0 z-[100] overflow-hidden" style="display: none;">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-coffee-900/80 backdrop-blur-md" 
             @click="cartOpen = false" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>
        
        <!-- Drawer Panel -->
        <div class="absolute right-0 top-0 bottom-0 w-full max-w-md glass-panel border-l border-white/10 flex flex-col bg-coffee-800/90"
             x-transition:enter="transition ease-out duration-400 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">
            
            <!-- Cart Header -->
            <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between bg-coffee-900/50">
                <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full bg-gold/10 text-gold flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </span>
                    Pesanan Anda
                </h2>
                <button @click="cartOpen = false" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-white rounded-full hover:bg-white/10 transition-all rotate-0 hover:rotate-90 duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-center opacity-50">
                        <div class="w-32 h-32 mb-6 opacity-30 text-gold">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">Keranjang Kosong</h3>
                        <p class="text-gray-400 mb-8">Belum ada kopi yang Anda pilih hari ini.</p>
                        <button @click="cartOpen = false" class="px-8 py-3 bg-white/5 border border-white/10 text-white rounded-full hover:bg-white/10 transition-colors font-bold">Pilih Menu</button>
                    </div>
                </template>

                <div class="space-y-5">
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="bg-coffee-900/50 border border-white/5 rounded-2xl p-5 flex gap-5 relative group overflow-hidden" x-transition.scale.origin.top>
                            <!-- Decorative glow -->
                            <div class="absolute -left-10 w-20 h-20 bg-gold/10 rounded-full blur-xl group-hover:bg-gold/20 transition-colors pointer-events-none"></div>

                            <div class="flex-1 relative z-10">
                                <h4 class="font-bold text-lg text-white mb-1" x-text="item.name"></h4>
                                <p class="text-gold font-bold mb-4" x-text="'Rp ' + item.price.toLocaleString('id-ID')"></p>
                                
                                <div class="flex items-center gap-1 bg-black/40 rounded-full w-max border border-white/10 p-1">
                                    <button @click="updateCart(index, -1)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition">-</button>
                                    <span class="w-8 text-center text-sm font-extrabold text-white" x-text="item.quantity"></span>
                                    <button @click="updateCart(index, 1)" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition">+</button>
                                </div>
                            </div>
                            
                            <div class="relative z-10 flex flex-col justify-between items-end">
                                <button @click="cart.splice(index, 1)" class="w-8 h-8 flex items-center justify-center bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-full transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                <span class="font-bold text-white text-sm" x-text="'Rp ' + (item.price * item.quantity).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Checkout Footer -->
            <div class="p-8 bg-coffee-900 border-t border-gold/20 relative overflow-hidden" x-show="cart.length > 0">
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-gold/10 rounded-full blur-[50px] pointer-events-none"></div>
                
                <!-- Guest Customer Info Input -->
                <div class="mb-5 relative z-10">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gold mb-2">Nama Pelanggan & Nomor Meja</label>
                    <input type="text" x-model="customerName" placeholder="Contoh: Meja 3 - Andi" class="w-full bg-[#1C1513] border border-white/10 focus:border-gold rounded-xl px-4 py-3 text-white placeholder-gray-600 focus:outline-none transition-colors text-sm">
                </div>
                
                <div class="flex justify-between items-end mb-6 relative z-10">
                    <span class="text-gray-400 text-sm font-semibold uppercase tracking-wider">Total</span>
                    <span class="text-3xl font-black text-white" x-text="'Rp ' + cartTotal.toLocaleString('id-ID')"></span>
                </div>
                
                <div class="space-y-3 relative z-10">
                    <button @click="openDirectQrisModal" :disabled="processing" class="w-full py-4 bg-gold hover:bg-gold-light text-coffee-900 rounded-2xl font-extrabold text-lg tracking-wide shadow-[0_0_20px_rgba(212,175,55,0.4)] transition-all flex justify-center items-center group overflow-hidden">
                        <span class="absolute inset-0 w-full h-full bg-white/20 -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-out"></span>
                        <span x-show="!processing" class="relative flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            Bayar QRIS Instant
                        </span>
                        <span x-show="processing" class="flex items-center gap-3 relative">
                            <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                    
                    <button @click="processOnlineCheckout" :disabled="processing" class="w-full py-3.5 bg-white/5 hover:bg-white/10 border border-white/20 text-white rounded-2xl font-bold text-base tracking-wide transition-all flex justify-center items-center">
                        <span x-show="!processing" class="relative">Pesan Metode Lain (Snap)</span>
                        <span x-show="processing" class="flex items-center gap-3 relative">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Section Simplified for Premium Feel -->
    <section id="reviews" class="py-32 bg-coffee-900 relative">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="text-center mb-20 reveal">
                <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6">Testimoni <span class="font-serif italic text-gold">Pelanggan</span></h2>
                <div class="w-20 h-1 bg-gold mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 reveal delay-100">
                @forelse($testimonials->take(3) as $review)
                <div class="glass-card p-8 rounded-3xl relative">
                    <div class="absolute -top-6 right-8 text-8xl text-white/5 font-serif leading-none">"</div>
                    <div class="flex items-center gap-1 mb-6">
                        @for($i=1; $i<=5; $i++)
                            <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-gold' : 'text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                    <p class="text-gray-300 mb-8 font-light leading-relaxed line-clamp-4">"{{ $review->comment }}"</p>
                    <div class="flex items-center gap-4 mt-auto border-t border-white/5 pt-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gold to-coffee-600 flex items-center justify-center font-bold text-white shadow-lg">
                            {{ substr($review->user->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-white">{{ $review->user->name }}</h4>
                            <span class="text-xs text-gold uppercase tracking-wider">Premium Member</span>
                        </div>
                    </div>
                </div>
                @empty
                <!-- Fallback Testimonials -->
                <div class="glass-card p-8 rounded-3xl relative">
                    <div class="absolute -top-6 right-8 text-8xl text-white/5 font-serif leading-none">"</div>
                    <div class="flex items-center gap-1 mb-6">
                        @for($i=1; $i<=5; $i++)
                            <svg class="w-5 h-5 text-gold" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                    <p class="text-gray-300 mb-8 font-light leading-relaxed">"Kopi paling otentik yang pernah saya coba di Jakarta. Suasananya sangat mendukung untuk nugas!"</p>
                    <div class="flex items-center gap-4 mt-auto border-t border-white/5 pt-4">
                        <div class="w-12 h-12 rounded-full bg-gold flex items-center justify-center font-bold text-coffee-900 shadow-lg">A</div>
                        <div>
                            <h4 class="font-bold text-white">Nida</h4>
                            <span class="text-xs text-gold uppercase tracking-wider">Pecinta Kopi</span>
                        </div>
                    </div>
                </div>
                <div class="glass-card p-8 rounded-3xl relative">
                    <div class="absolute -top-6 right-8 text-8xl text-white/5 font-serif leading-none">"</div>
                    <div class="flex items-center gap-1 mb-6">
                        @for($i=1; $i<=5; $i++)
                            <svg class="w-5 h-5 text-gold" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @endfor
                    </div>
                    <p class="text-gray-300 mb-8 font-light leading-relaxed">"ZCoffee Hening bener-bener gila! Harganya bersahabat tapi rasanya bintang lima. Favorit saya Caramel Macchiato-nya."</p>
                    <div class="flex items-center gap-4 mt-auto border-t border-white/5 pt-4">
                        <div class="w-12 h-12 rounded-full bg-gold flex items-center justify-center font-bold text-coffee-900 shadow-lg">B</div>
                        <div>
                            <h4 class="font-bold text-white">Dwi Luthfiana Furqon</h4>
                            <span class="text-xs text-gold uppercase tracking-wider">Pelanggan Setia</span>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Premium Footer -->
    <footer class="bg-[#0A0706] pt-20 pb-10 border-t border-white/5">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-16">
                <div class="text-2xl md:text-3xl font-extrabold tracking-widest text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gold flex items-center justify-center text-coffee-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    ZCOFFE<span class="text-gold">HENING</span>
                </div>
                <div class="flex gap-6">
                    <a href="#" class="w-12 h-12 rounded-full glass-panel flex items-center justify-center text-gray-400 hover:text-gold hover:border-gold/50 transition-all">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 448 512">
                            <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/>
                        </svg>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-full glass-panel flex items-center justify-center text-gray-400 hover:text-gold hover:border-gold/50 transition-all"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                </div>
            </div>
            <div class="text-center">
                <p class="text-gray-600 text-sm">© {{ date('Y') }} ZCoffee Hening System. Dibuat dengan <span class="text-gold">♥</span> untuk pecinta kopi.</p>
                <p class="text-gray-700 text-[10px] mt-4 uppercase tracking-[0.2em] font-medium opacity-50">
                    dikembangkan oleh Ari Fujiyono Mahasiswa Universitas Darunnajah
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Intersection Observer for scroll animations
        document.addEventListener("DOMContentLoaded", () => {
            const observerOptions = { root: null, rootMargin: '0px', threshold: 0.1 };
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        });

        // Main App Logic
        function storeApp() {
            return {
                mobileMenuOpen: false,
                customerName: '',
                scrolled: false,
                activeCategory: 'all',
                products: @json($products),
                cartOpen: false,
                cart: JSON.parse(localStorage.getItem('art_coffee_cart')) || [],
                processing: false,
                qrisModalOpen: false,
                qrisQrUrl: '',
                qrisAmount: 0,
                qrisOrderNumber: '',
                qrisCountdown: 900,
                qrisCountdownStr: '15:00',
                qrisStatus: 'pending',
                qrisInterval: null,
                qrisTimer: null,

                init() {
                    this.$watch('cart', val => {
                        localStorage.setItem('art_coffee_cart', JSON.stringify(val));
                    }, { deep: true });
                },

                get filteredProducts() {
                    if (this.activeCategory === 'all') return this.products;
                    return this.products.filter(p => p.category_id == this.activeCategory);
                },

                get cartTotalItems() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },

                get cartTotal() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                addToCart(product) {
                    const existing = this.cart.find(i => i.id === product.id);
                    if (existing) {
                        if (existing.quantity < product.stock) {
                            existing.quantity++;
                        } else {
                            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'warning', title: 'Stok maksimum tercapai!' });
                        }
                    } else {
                        this.cart.push({ id: product.id, name: product.name, price: product.price, quantity: 1, stock: product.stock });
                    }
                    this.cartOpen = true;
                },

                updateCart(index, delta) {
                    const item = this.cart[index];
                    const newQty = item.quantity + delta;
                    if (newQty > 0 && newQty <= item.stock) {
                        item.quantity = newQty;
                    } else if (newQty === 0) {
                        this.cart.splice(index, 1);
                    }
                },

                async processOnlineCheckout() {
                    if (!this.customerName.trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nama / Meja Kosong',
                            text: 'Silakan masukkan Nama Pelanggan atau Nomor Meja terlebih dahulu.',
                            confirmButtonColor: '#D4AF37',
                            background: '#1C1513',
                            color: '#fff'
                        });
                        return;
                    }
                    this.processing = true;
                    try {
                        const response = await axios.post('{{ route("checkout.online") }}', {
                            customer_name: this.customerName,
                            items: this.cart
                        });

                        if (response.data.success) {
                            const snapToken = response.data.snap_token;
                            const orderNumber = response.data.order_number;
                            
                            this.cart = [];
                            this.cartOpen = false;

                            // Call Midtrans Snap pay popup
                            window.snap.pay(snapToken, {
                                onSuccess: function(result) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Pembayaran Sukses!',
                                        text: 'Terima kasih, pembayaran Anda telah kami terima.',
                                        confirmButtonColor: '#D4AF37',
                                        background: '#1C1513',
                                        color: '#fff'
                                    }).then(() => {
                                        window.location.href = '/orders/' + orderNumber + '/invoice';
                                    });
                                },
                                onPending: function(result) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Pembayaran Tertunda',
                                        text: 'Silakan selesaikan pembayaran Anda.',
                                        confirmButtonColor: '#D4AF37',
                                        background: '#1C1513',
                                        color: '#fff'
                                    }).then(() => {
                                        window.location.href = '/orders/' + orderNumber + '/invoice';
                                    });
                                },
                                onError: function(result) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Pembayaran Gagal',
                                        text: 'Terjadi kesalahan saat memproses pembayaran.',
                                        confirmButtonColor: '#D4AF37',
                                        background: '#1C1513',
                                        color: '#fff'
                                    });
                                },
                                onClose: function() {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Pembayaran Belum Selesai',
                                        text: 'Anda menutup popup pembayaran. Anda dapat melanjutkannya di halaman invoice.',
                                        confirmButtonColor: '#D4AF37',
                                        background: '#1C1513',
                                        color: '#fff'
                                    }).then(() => {
                                        window.location.href = '/orders/' + orderNumber + '/invoice';
                                    });
                                }
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Mohon Maaf',
                            text: error.response?.data?.message || 'Terjadi kesalahan pada sistem. Silakan coba lagi.',
                            confirmButtonColor: '#D4AF37',
                            background: '#1C1513',
                            color: '#fff'
                        });
                    } finally {
                        this.processing = false;
                    }
                },

                async openDirectQrisModal() {
                    if (!this.customerName.trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nama / Meja Kosong',
                            text: 'Silakan masukkan Nama Pelanggan atau Nomor Meja terlebih dahulu.',
                            confirmButtonColor: '#D4AF37',
                            background: '#1C1513',
                            color: '#fff'
                        });
                        return;
                    }
                    this.processing = true;
                    try {
                        const response = await axios.post('{{ route("checkout.qris") }}', {
                            customer_name: this.customerName,
                            items: this.cart
                        });

                        if (response.data.success) {
                            this.qrisQrUrl = response.data.qr_url;
                            this.qrisAmount = response.data.total_amount;
                            this.qrisOrderNumber = response.data.order_number;
                            this.qrisStatus = 'pending';
                            
                            this.qrisCountdown = 900;
                            this.qrisCountdownStr = '15:00';
                            
                            this.cart = [];
                            this.cartOpen = false;
                            this.qrisModalOpen = true;

                            if (this.qrisTimer) clearInterval(this.qrisTimer);
                            this.qrisTimer = setInterval(() => {
                                if (this.qrisCountdown > 0) {
                                    this.qrisCountdown--;
                                    const minutes = Math.floor(this.qrisCountdown / 60);
                                    const seconds = this.qrisCountdown % 60;
                                    this.qrisCountdownStr = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
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

                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Pembayaran Berhasil!',
                                                text: 'Terima kasih, transaksi QRIS telah diverifikasi secara real-time.',
                                                confirmButtonColor: '#D4AF37',
                                                background: '#1C1513',
                                                color: '#fff'
                                            }).then(() => {
                                                window.location.href = `/orders/${this.qrisOrderNumber}/invoice`;
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Pembayaran Gagal / Expired',
                                                text: 'Transaksi ini telah gagal atau waktu pembayaran habis.',
                                                confirmButtonColor: '#D4AF37',
                                                background: '#1C1513',
                                                color: '#fff'
                                            });
                                        }
                                    }
                                } catch (err) {
                                    console.error("Gagal polling status QRIS:", err);
                                }
                            }, 2000);
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Mohon Maaf',
                            text: error.response?.data?.message || 'Terjadi kesalahan saat memproses QRIS.',
                            confirmButtonColor: '#D4AF37',
                            background: '#1C1513',
                            color: '#fff'
                        });
                    } finally {
                        this.processing = false;
                    }
                }
            }
        }
    </script>

    <!-- QRIS Direct Realtime Modal -->
    <div x-show="qrisModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" x-cloak>
        <div class="bg-[#1C1513] border border-white/10 w-full max-w-md p-6 sm:p-8 rounded-3xl shadow-2xl relative space-y-6 text-center" @click.away="qrisModalOpen = false; clearInterval(qrisInterval); clearInterval(qrisTimer);">
            <button @click="qrisModalOpen = false; clearInterval(qrisInterval); clearInterval(qrisTimer);" class="absolute top-4 right-4 text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- Invoice details -->
            <div class="space-y-1">
                <h3 class="text-xs uppercase tracking-widest text-gray-400 font-bold">Pembayaran QRIS Instant</h3>
                <p class="text-sm font-semibold text-amber-500" x-text="qrisOrderNumber"></p>
                <div class="text-3xl font-black text-white mt-2" x-text="'Rp ' + parseInt(qrisAmount).toLocaleString('id-ID')"></div>
            </div>

            <!-- QR code image container with rounded border -->
            <div class="flex justify-center items-center bg-white p-4 rounded-2xl w-60 h-60 mx-auto relative shadow-inner">
                <img :src="qrisQrUrl" alt="QRIS Code" class="w-full h-full object-contain" />
                
                <!-- Overlay Success or Expired state -->
                <div x-show="qrisStatus === 'success'" class="absolute inset-0 bg-[#1C1513]/90 rounded-2xl flex flex-col justify-center items-center space-y-2">
                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white shadow-[0_0_20px_rgba(34,197,94,0.4)] animate-pulse">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-green-400 font-bold text-sm">Pembayaran Berhasil!</p>
                </div>

                <div x-show="qrisStatus === 'expired'" class="absolute inset-0 bg-[#1C1513]/90 rounded-2xl flex flex-col justify-center items-center space-y-2">
                    <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center text-white">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <p class="text-red-400 font-bold text-sm">QRIS Expired / Gagal</p>
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
                    <span class="text-sm font-bold text-gray-300"
                          x-text="qrisStatus === 'pending' ? 'Menunggu Pembayaran...' : (qrisStatus === 'success' ? 'Pembayaran Sukses' : 'Kedaluwarsa')"></span>
                </div>

                <!-- Countdown Timer -->
                <div class="text-xs text-gray-400">
                    Selesaikan pembayaran dalam <span class="font-extrabold text-amber-500 text-sm" x-text="qrisCountdownStr">15:00</span>
                </div>

                <div class="flex justify-center items-center gap-1.5 text-xs text-gray-500 border-t border-white/5 pt-4">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg" alt="QRIS" class="h-6 object-contain filter invert opacity-80" />
                    <span>Scan menggunakan GoPay, ShopeePay, Dana, OVO atau M-Banking</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
