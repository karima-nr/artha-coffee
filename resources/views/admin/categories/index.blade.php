@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Kategori Kopi</h1>
    <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">
        + Tambah Kategori
    </button>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">ID</th>
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Nama Kategori</th>
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Slug</th>
                <th class="p-4 text-sm font-semibold text-gray-600 dark:text-gray-300">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($categories as $cat)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="p-4 text-gray-900 dark:text-white">{{ $cat->id }}</td>
                <td class="p-4 text-gray-900 dark:text-white">{{ $cat->name }}</td>
                <td class="p-4 text-gray-500 dark:text-gray-400">{{ $cat->slug }}</td>
                <td class="p-4 flex gap-2">
                    <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Yakin hapus?');">
                        @csrf @method('DELETE')
                        <button class="text-red-500 hover:text-red-700 text-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
        {{ $categories->links() }}
    </div>
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tambah Kategori</h3>
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kategori</label>
                <input type="text" name="name" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
