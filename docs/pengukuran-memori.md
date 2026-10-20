# Pengukuran Memori: get() vs lazyById()

Dataset: sekitar 52.500 baris di tabel `transactions` (2.500 dari seeder + 50.000 data dummy).

| Cara baca data | Memori puncak (MB) |
| `Transaction::lazyById()` | 70,3 |
| `Transaction::get()` | 128,6 |

Group conclusion: For the same 52,500 rows, `get()` uses a peak memory of about 128.6 MB, while `lazyById()` uses about 70.3 MB, so `get()` uses about 58 MB (roughly 1.8 times) more. The difference occurs because `get()` loads all rows into memory at once as a single Collection, while `lazyById()` fetches data in small chunks and discards the previous chunk, keeping memory usage relatively stable. The `lazyById()` figure isn’t as small as one might expect because the base memory of the Tinker session (including leftover dummy data created earlier) is included in the peak. Even so, the difference is still clear, and it will widen as the data grows until `get()` hits PHP’s `memory_limit`. Therefore, for exporting or processing large datasets, we use `lazyById()`, while `get()` is sufficient for small datasets that truly need to be loaded in their entirety.
