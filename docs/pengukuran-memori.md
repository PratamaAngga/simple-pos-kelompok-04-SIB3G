# Pengukuran Memori: get() vs lazyById()

Dataset: sekitar 52.500 baris di tabel `transactions` (2.500 dari seeder + 50.000 data dummy).

| Cara baca data | Memori puncak (MB) |
| `Transaction::lazyById()` | 70,3 |
| `Transaction::get()` | 128,6 |

Kesimpulan kelompok: untuk 52.500 baris yang sama, `get()` memakai memori puncak sekitar 128,6 MB, sedangkan `lazyById()` sekitar 70,3 MB, jadi `get()` memakai sekitar 58 MB (kurang lebih 1,8 kali) lebih banyak. Selisihnya terjadi karena `get()` memuat seluruh baris ke memori sekaligus sebagai satu Collection, sementara `lazyById()` mengambil data per potongan kecil (chunk) dan membuang potongan sebelumnya, sehingga pemakaian memori tetap relatif stabil. Angka `lazyById()` tidak sekecil yang dibayangkan karena memori dasar sesi tinker (termasuk sisa data dummy yang dibuat sebelumnya) ikut terhitung dalam puncak. Meski begitu, selisihnya tetap jelas, dan akan makin lebar seiring bertambahnya data hingga `get()` bisa menabrak `memory_limit` PHP. Karena itu, untuk ekspor atau pemrosesan data besar kami memakai `lazyById()`, sedangkan `get()` cukup untuk data kecil yang memang perlu dimuat seluruhnya.
