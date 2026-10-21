
--- Advanced Web Programming ---
Group Practicum Jobsheet: Meeting 6 (Group 4 - SIB 3G)

---  Step 6 (Part 4): Testing POST /pos with curl  ---

I sent POST /pos requests with curl after fetching the CSRF token and session cookie from the /pos page, using the Accept: application/json header so Laravel responds with JSON. In the first case, I set qty to 0 with a valid product_id. The server responded with 422 Unprocessable Content and the message The items.0.qty field must be at least 1., which comes from the min:1 rule on items.*.qty. In the second case, I sent product_id=99999, which does not exist in the database. The server again responded with 422, this time with The selected items.0.product_id is invalid., from the exists:products,id rule.

The error keys use dot notation (items.0.qty and items.0.product_id), where the 0 indicates which cart row has the problem. Without the JSON header, Laravel responds with 302 and stores the error messages in the session, which is what a browser experiences. After both tests, Transaction::count() in tinker was still 2500, the same as before the requests, so requests rejected by validation do not save any data.

---  Step 6 (Part 5): Is a total field with the numeric rule enough to prevent manipulation?  ---

No, it is not enough. The numeric rule only checks the shape of the data, meaning whether the value is a number, not whether that number is correct. The client is fully under the user's control: with curl or browser DevTools, anyone can send total=1 for a Rp500,000 purchase, and that value still passes both numeric and min:0, so the server would store a fake total. Sending per-item prices from the form does not help either, since those can be forged too.

The solution is to trust no number from the client. The client only sends product_id and qty, and the server fetches prices from the database, computes price × qty for each row, and sums them into the total, as TransactionController::store() does. Because total is in $fillable, code like Transaction::create($request->all()) must also be avoided, since it would store the client-supplied total as is.

## Skenario Uji Manual (Autentikasi & RBAC)

Jalankan `php artisan migrate:fresh --seed` lalu `php artisan serve`.
Semua akun demo memakai kata sandi `password`.

### Admin (admin@pos.test)
| Langkah | Hasil yang diharapkan |
|---|---|
| Login lewat `/login` | Masuk, diarahkan ke `/pos` |
| Buka `/products` dan `/categories` | Halaman tampil normal |
| Lihat navigasi | Menu Produk dan Kategori muncul |

### Kasir (kasir@pos.test)
| Langkah | Hasil yang diharapkan |
|---|---|
| Login lewat `/login` | Masuk, diarahkan ke `/pos` |
| Buka `/pos` dan `/transactions` | Halaman tampil normal |
| Buka `/products` atau `/categories` | Error 403 |
| Lihat navigasi | Menu Produk dan Kategori tidak muncul |

### Manager (manager@pos.test)
| Langkah | Hasil yang diharapkan |
|---|---|
| Login lewat `/login` | Masuk, diarahkan ke `/pos` |
| Buka `/transactions` | Halaman tampil normal |
| Buka `/products` atau `/categories` | Error 403 |

### Tamu (belum login)
| Langkah | Hasil yang diharapkan |
|---|---|
| Buka `/products` atau `/pos` | Diarahkan ke `/login` |
| `curl -i http://127.0.0.1:8000/pos -H "Accept: application/json"` | Status 401, isi `{"message":"Unauthenticated."}` |
| Login dengan kata sandi salah | Pesan "Email atau kata sandi salah." |

### Akun nonaktif
| Langkah | Hasil yang diharapkan |
|---|---|
| Set `is_active` = false lewat tinker, lalu login | Pesan "Akun dinonaktifkan. Hubungi admin." |

## Usulan Proses yang Layak Menggunakan Queue (JOBSHEET 8)

### Proses yang diusulkan: pengiriman struk transaksi lewat email

Setelah kasir menyelesaikan transaksi di halaman '/pos', sistem
mengirim struk (ringkasan item, total, dan waktu transaksi) ke email pelanggan.

### Mengapa proses ini layak masuk queue
1. Lambat dan bergantung pada pihak luar. Pengiriman email harus menghubungi server SMTP. Waktunya tidak bisa diprediksi, kadang cepat, kadang beberapa detik, dan bisa gagal kalau server email sedang bermasalah.
2. Kasir tidak boleh menunggu . Tanpa queue, tombol "Bayar" baru selesai setelah email terkirim. Di jam ramai, antrean pelanggan di kasir menjadi lebih panjang hanya karena menunggu email.
3. Kegagalan email tidak boleh membatalkan transaksi. Transaksi dan stok sudah tersimpan di database. Kalau email gagal, yang perlu diulang hanyalah pengiriman emailnya, bukan seluruh transaksi.
4. Aman diulang. Mengirim ulang struk yang sama tidak mengubah data di database, sehingga job boleh dicoba beberapa kali.
5. Sama polanya dengan import produk. Seperti 'ProcessProductImport', proses cukup menerima ID transaksi, lalu mengerjakan sisanya di background, dengan '$tries' dan '$backoff' untuk percobaan ulang. Job yang tetap gagal tercatat di 'failed_jobs' sehingga bisa ditelusuri.

### Rancangan singkat
- Setelah transaksi tersimpan di 'TransactionController: :store()', panggil `SendReceiptEmail: :dispatch($transaction->id);'.
- Job 'SendReceiptEmail' menerima ** ID transaksi saja ** (bukan seluruh data), memuat transaksi beserta item dan kasirnya, lalu mengirim email.
- Dengan '$tries = 3' dan '$backoff = [10, 60]', email dicoba ulang kalau server SMTP sedang bermasalah.
- Kasir langsung melihat konfirmasi "Transaksi berhasil" tanpa menunggu email.

### Alternatif lain yang juga cocok
Rekap penjualan harian otomatis. Menghitung total penjualan seluruh hari dan membuat file PDF atau Excel-nya membutuhkan waktu dan memori, sehingga sebaiknya dijalankan di background pada jam sepi (misalnya malam hari), bukan saat admin menunggu di browser.

### Perlu diingat
Queue membutuhkan worker yang selalu berjalan (`php artisan queue:work'). Pastikan 'QUEUE_CONNECTION=database' dan worker di-restart setiap kali kode job berubah. Di server produksi worker dijalankan lewat process manager seperti Supervisor.

## Diskusi: Format Laporan untuk 100.000 Transaksi (STEP 6)

Kami memilih ** CSV **. CSV bisa dialirkan per baris dengan 'streamDownload()' dan `lazyById()`, sehingga pemakaian memori tetap kecil berapa pun jumlah datanya. Excel masih bisa dipakai karena Laravel Excel membaca data per potongan lewat 'FromQuery', tetapi prosesnya lebih berat. PDF tidak cocok karena DomPDF menyimpan seluruh dokumen di memori, sehingga laporan sebesar itu berisiko kehabisan memori dan menghasilkan dokumen yang terlalu panjang untuk dibaca.