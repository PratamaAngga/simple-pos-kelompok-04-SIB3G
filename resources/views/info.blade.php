<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi Simple POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-slate-100 px-4">
    <div class="w-full max-w-md bg-white rounded-md shadow p-6">
        <h1 class="text-lg font-semibold mb-2">Tentang Simple POS</h1>
        <p class="text-sm text-slate-600 mb-4">
            Simple POS adalah aplikasi kasir sederhana untuk mencatat transaksi penjualan kafe.
            Silakan masuk untuk menggunakan aplikasi.
        </p>
        <a href="{{ route('login') }}" class="text-sm text-slate-900 underline">Ke halaman login</a>
    </div>
</body>

</html>