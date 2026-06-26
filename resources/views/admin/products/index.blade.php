@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Manajemen Produk & Stok</h1>
    <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">
        + Tambah Produk
    </button>
</div>

@if ($errors->any())
<div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada input Anda:</h3>
            <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-x-auto">
    <table class="w-full text-left border-collapse whitespace-nowrap">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Menu</th>
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Kategori</th>
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Harga</th>
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Stok</th>
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Status</th>
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($products as $product)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-4 text-gray-900 dark:text-white font-medium flex items-center gap-3">
                    @if($product->image)
                        <img src="{{ asset('Images/' . $product->image) }}" class="w-10 h-10 rounded-lg object-cover border border-gray-100 dark:border-gray-700">
                    @else
                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                    {{ $product->name }}
                </td>
                <td class="p-4 text-gray-500 dark:text-gray-400">{{ $product->category->name ?? '-' }}</td>
                <td class="p-4 text-gray-900 dark:text-white">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                <td class="p-4">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-900 dark:text-white">{{ $product->stock }}</span>
                        <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden dark:bg-gray-700">
                            <div class="h-2 bg-{{ $product->stock > 20 ? 'green' : ($product->stock > 0 ? 'yellow' : 'red') }}-500" style="width: {{ min(100, $product->stock) }}%"></div>
                        </div>
                    </div>
                </td>
                <td class="p-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $product->status == 'ready' ? 'bg-green-100 text-green-800' : ($product->status == 'limited' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ strtoupper($product->status) }}
                    </span>
                </td>
                <td class="p-4 flex gap-3">
                    <button onclick="editProduct({{ $product->toJson() }})" class="text-blue-500 hover:text-blue-700 text-sm">Edit</button>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Yakin hapus produk ini?');">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700 text-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
        {{ $products->links() }}
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" id="modalTitle">Tambah Produk</h3>
        <form action="{{ route('admin.products.store') }}" method="POST" id="productForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="POST" id="methodInput">
            
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Produk</label>
                    <input type="text" name="name" id="p_name" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                    <select name="category_id" id="p_category" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm font-medium pointer-events-none">Rp</span>
                        <input type="text" id="p_price_display" inputmode="numeric" required
                               placeholder="12.000"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-9 pr-3">
                    </div>
                    <input type="hidden" name="price" id="p_price">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stok Awal</label>
                    <input type="number" name="stock" id="p_stock" required min="0" value="100" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" id="p_status" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="ready">Ready</option>
                        <option value="limited">Limited</option>
                        <option value="sold_out">Sold Out</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                    <textarea name="description" id="p_description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gambar Produk</label>

                    <!-- Drop Zone -->
                    <div id="dropZone"
                         class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl overflow-hidden cursor-pointer transition-all duration-300 hover:border-amber-400 dark:hover:border-amber-500 group"
                         onclick="document.getElementById('p_image').click()"
                         ondragover="event.preventDefault(); this.classList.add('border-amber-400','bg-amber-50','dark:bg-amber-900/10')"
                         ondragleave="this.classList.remove('border-amber-400','bg-amber-50','dark:bg-amber-900/10')"
                         ondrop="handleDrop(event)">

                        <!-- State: Tidak ada gambar -->
                        <div id="dropPlaceholder" class="flex flex-col items-center justify-center py-10 px-4 text-center">
                            <div class="w-14 h-14 mb-3 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Klik atau seret gambar ke sini</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP &bull; Maks. 2MB</p>
                        </div>

                        <!-- State: Ada pratinjau -->
                        <div id="previewContainer" class="hidden relative">
                            <img id="previewImage" src="" alt="Pratinjau" class="w-full h-56 object-cover">
                            <!-- Overlay saat hover -->
                            <div class="absolute inset-0 bg-black/50 opacity-0 hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                <span class="text-white text-sm font-semibold">Ganti Gambar</span>
                            </div>
                            <!-- Badge nama file -->
                            <div id="fileNameBadge" class="hidden absolute bottom-2 left-2 right-2 bg-black/60 backdrop-blur-sm text-white text-xs px-3 py-1.5 rounded-lg truncate"></div>
                        </div>
                    </div>

                    <!-- Input file tersembunyi -->
                    <input type="file" name="image" id="p_image" accept="image/jpeg,image/png,image/webp" class="hidden">

                    <!-- Tombol hapus pratinjau (muncul jika ada preview) -->
                    <button type="button" id="clearImageBtn"
                            onclick="clearImagePreview()"
                            class="hidden mt-2 text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Hapus gambar baru
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ── Format Helpers ──────────────────────────────────────────────────────
    function formatRupiah(value) {
        // Strip semua non-digit
        const digits = String(value).replace(/\D/g, '');
        if (!digits) return '';
        // Format ribuan dengan titik
        return parseInt(digits, 10).toLocaleString('id-ID');
    }

    function getRawPrice() {
        const display = document.getElementById('p_price_display').value;
        return display.replace(/\./g, '').replace(/,/g, '') || '0';
    }

    // ── Live formatting saat user mengetik ──────────────────────────────────
    document.getElementById('p_price_display').addEventListener('input', function () {
        const raw = this.value.replace(/\./g, '').replace(/,/g, '');
        const formatted = formatRupiah(raw);
        // Simpan posisi kursor agar tidak loncat ke akhir
        const selStart = this.selectionStart;
        const prevLen  = this.value.length;
        this.value = formatted;
        // Geser kursor proporsional dengan perubahan panjang
        const diff = formatted.length - prevLen;
        const newPos = Math.max(0, selStart + diff);
        this.setSelectionRange(newPos, newPos);
        // Sinkron ke hidden input
        document.getElementById('p_price').value = raw.replace(/\D/g, '');
    });

    // ── Pastikan hidden input terisi sebelum form submit ────────────────────
    document.getElementById('productForm').addEventListener('submit', function () {
        document.getElementById('p_price').value = getRawPrice();
    });

    // ── Tutup modal ─────────────────────────────────────────────────────────
    function closeModal() {
        document.getElementById('createModal').classList.add('hidden');
        document.getElementById('productForm').reset();
        document.getElementById('p_price_display').value = '';
        document.getElementById('p_price').value = '';
        document.getElementById('productForm').action = "{{ route('admin.products.store') }}";
        document.getElementById('methodInput').value = "POST";
        document.getElementById('modalTitle').innerText = "Tambah Produk";
        clearImagePreview();
    }

    // ── Isi modal saat edit ─────────────────────────────────────────────────
    function editProduct(product) {
        document.getElementById('createModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = "Edit Produk";
        document.getElementById('methodInput').value = "PUT";
        document.getElementById('productForm').action = `/admin/products/${product.id}`;

        document.getElementById('p_name').value         = product.name;
        document.getElementById('p_category').value     = product.category_id;
        document.getElementById('p_stock').value        = product.stock;
        document.getElementById('p_status').value       = product.status;
        document.getElementById('p_description').value  = product.description || '';

        // Tampilkan harga terformat di display input, simpan angka di hidden input
        const rawPrice = Math.round(parseFloat(product.price) || 0);
        document.getElementById('p_price_display').value = formatRupiah(rawPrice);
        document.getElementById('p_price').value         = rawPrice;

        // Tampilkan gambar lama sebagai pratinjau awal
        if (product.image) {
            showPreview(`/Images/${product.image}`, null);
        } else {
            clearImagePreview();
        }
    }

    // ── Pratinjau gambar ────────────────────────────────────────────────────
    function showPreview(src, fileName) {
        document.getElementById('dropPlaceholder').classList.add('hidden');
        document.getElementById('previewContainer').classList.remove('hidden');
        document.getElementById('previewImage').src = src;

        const badge = document.getElementById('fileNameBadge');
        if (fileName) {
            badge.textContent = fileName;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }

        // Tampilkan tombol hapus hanya untuk gambar baru (ada fileName)
        const clearBtn = document.getElementById('clearImageBtn');
        if (fileName) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    function clearImagePreview() {
        document.getElementById('dropPlaceholder').classList.remove('hidden');
        document.getElementById('previewContainer').classList.add('hidden');
        document.getElementById('previewImage').src = '';
        document.getElementById('fileNameBadge').textContent = '';
        document.getElementById('clearImageBtn').classList.add('hidden');
        // Reset file input
        const fileInput = document.getElementById('p_image');
        fileInput.value = '';
    }

    // ── Listener file input ─────────────────────────────────────────────────
    document.getElementById('p_image').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        // Validasi ukuran (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => showPreview(e.target.result, file.name);
        reader.readAsDataURL(file);
    });

    // ── Drag & Drop handler ─────────────────────────────────────────────────
    function handleDrop(event) {
        event.preventDefault();
        const dropZone = document.getElementById('dropZone');
        dropZone.classList.remove('border-amber-400', 'bg-amber-50', 'dark:bg-amber-900/10');

        const file = event.dataTransfer.files[0];
        if (!file || !file.type.startsWith('image/')) {
            alert('Hanya file gambar yang diterima (JPG, PNG, WEBP).');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            return;
        }

        // Assign file ke input agar ikut ter-submit
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        document.getElementById('p_image').files = dataTransfer.files;

        const reader = new FileReader();
        reader.onload = (e) => showPreview(e.target.result, file.name);
        reader.readAsDataURL(file);
    }
</script>
@endsection
