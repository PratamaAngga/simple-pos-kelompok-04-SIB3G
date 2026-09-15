<nav class="bg-white border-b border-gray-200 px-6 py-4">
    <!-- flex & items-center untuk merapikan posisi, space-x-6 untuk memberi jarak antar link -->
    <div class="flex items-center space-x-6">
        
        <!-- Link POS / Kasir -->
        <a href="{{ Route::has('pos') ? route('pos') : '/pos' }}" 
           class="{{ request()->is('pos') || request()->routeIs('pos') ? 'text-blue-600 font-semibold border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }} pb-1 transition">
            POS
        </a>

        <!-- Link Riwayat Transaksi -->
        <a href="{{ Route::has('transactions.index') ? route('transactions.index') : '#' }}" 
           class="{{ request()->routeIs('transactions.*') ? 'text-blue-600 font-semibold border-b-2 border-blue-600' : 'text-gray-600 hover:text-gray-900' }} pb-1 transition">
            Riwayat Transaksi
        </a>

    </div>
</nav>