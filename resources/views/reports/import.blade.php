@extends('layouts.app')

@section('title', 'Import Produk')

@section('content')
    <h1 class="text-lg font-semibold mb-4">Import Produk dari CSV</h1>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-3 rounded-md mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="max-w-md">
        @csrf
        <label class="block mb-3">
            <span class="text-sm font-medium">File CSV</span>
            <input
                type="file"
                name="csv"
                id="csv"
                accept=".csv"
                class="hidden"
            >

            <label
                for="csv"
                class="inline-block cursor-pointer rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Pilih File CSV
            </label>

            <span id="file-name" class="ml-2 text-sm text-gray-500">
                Belum ada file dipilih
            </span>

            <script>
                document.getElementById('csv').addEventListener('change', function () {
                    document.getElementById('file-name').textContent =
                        this.files.length > 0 ? this.files[0].name : 'Belum ada file dipilih';
                });
            </script>
            @error('csv')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </label>

        <p class="text-xs text-slate-500 mb-3">
            Column yang dibutuhkan: <code>name</code>, <code>category_id</code>, <code>price</code>.
        </p>

        <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm">
            Import
        </button>
    </form>

    <h2 class="text-lg font-semibold mt-8 mb-3">Riwayat Error Import</h2>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-slate-300 text-sm">
            <thead class="bg-slate-100">
                <tr>
                    <th class="border border-slate-300 p-2 text-left">File</th>
                    <th class="border border-slate-300 p-2 text-left">Nomor Baris</th>
                    <th class="border border-slate-300 p-2 text-left">Pesan Error</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($importErrors as $error)
                    <tr>
                        <td class="border border-slate-300 p-2">
                            {{ $error->source_file }}
                        </td>
                        <td class="border border-slate-300 p-2">
                            {{ $error->row_number }}
                        </td>
                        <td class="border border-slate-300 p-2">
                            {{ $error->message }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="border border-slate-300 p-3 text-center text-slate-500">
                            Belum ada error import.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection