# ShopEase · Pertemuan 10

Praktikum PBKK dengan CodeIgniter 4.7.6 dan Bootstrap 5.3.9. Pertemuan ini melatih routing eksplisit, parameter ID dinamis, pengiriman data dari controller ke view, dan penggunaan layout bersama.

## Fitur

- Landing page dengan hero, empat kategori, enam produk unggulan, dan navigasi responsif.
- Katalog dengan foto, harga Rupiah, stok, serta filter kategori sederhana. Contoh URL adalah `/products?category=olahraga`.
- Detail produk dengan breadcrumb dan tautan kembali ke katalog. ID yang tidak tersedia menghasilkan HTTP 404.
- Halaman Tentang ShopEase.

## Struktur aplikasi

| Bagian | Lokasi |
| --- | --- |
| Rute eksplisit dan pengaturan Auto Routing | `app/Config/Routes.php` |
| Controller halaman beranda, katalog, detail, dan tentang | `app/Controllers/` |
| Array kategori dan produk dengan ID sebagai kunci | `app/Config/Catalog.php` |
| View frontend, grid produk yang dipakai ulang, serta layout navbar dan footer | `app/Views/` |
| Bootstrap, stylesheet ShopEase, dan gambar WebP | `public/css/`, `public/images/shopease/` |

Controller membaca array dari `Catalog.php`, memilih data untuk rute yang diminta, lalu mengirimkannya ke view. Gambar dibuat dengan ImageGen bawaan dan disimpan lokal. Brief visualnya adalah foto studio satu produk tanpa logo pada latar slate muda untuk enam produk katalog, serta foto hero dengan headphone, sneakers, ransel navy, dan botol olahraga oranye.

## Rute

| Metode | URL | Handler |
| --- | --- | --- |
| GET | `/` | `HomeController::index` |
| GET | `/products` | `ProductController::index` |
| GET | `/products/(:num)` | `ProductController::detail/$1` |
| GET | `/about` | `HomeController::about` |

## Menjalankan secara lokal

Dari folder `Pertemuan 10`, jalankan `composer install` jika folder `vendor` belum tersedia. Gunakan PHP yang memenuhi persyaratan Composer dengan ekstensi `intl` dan `mbstring`.

```powershell
php spark serve
```

Buka [beranda](http://localhost:8080/) atau [contoh detail produk](http://localhost:8080/products/1). Aplikasi berjalan dengan konfigurasi bawaan tanpa MySQL atau file `.env`.

## Hasil pengujian Stage 2

- `php -l` lulus untuk 10 file PHP. `php spark routes` menampilkan empat rute GET dengan Auto Routing nonaktif di konfigurasi.
- Uji HTTP pada server Spark mengembalikan 200 untuk beranda, katalog, Tentang, detail ID 1–6, dua stylesheet, dan tujuh gambar WebP. ID yang hilang atau bukan angka, rute tak terdaftar, dan `POST /products` mengembalikan 404.
- Filter Olahraga menampilkan dua produk. Nilai kategori tak dikenal mengembalikan katalog lengkap.
- Chrome headless memeriksa tampilan pada 390, 768, dan 1280 piksel tanpa gulir horizontal atau gambar gagal dimuat. Alur klik kategori, katalog terfilter, dan detail berhasil.
- Uji HTTP berjalan dengan pengaturan driver database yang sengaja dibuat tidak valid di proses server uji.

## Batasan

Katalog, harga, dan stok adalah data simulasi. Pertemuan ini mencakup penelusuran produk. Penyimpanan database, autentikasi, pengelolaan produk, keranjang, checkout, dan transaksi berada di luar cakupan.
