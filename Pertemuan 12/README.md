# ShopEase · Pertemuan 12

**Authentication & Product Management** adalah praktikum PBKK tentang pengelolaan katalog dengan CodeIgniter 4. Pertemuan ini melanjutkan katalog dinamis Pertemuan 11 dengan registrasi Customer, dashboard berbasis peran, CRUD kategori dan produk, serta pengelolaan gambar.

## Teknologi dan fitur

| Teknologi | Penggunaan |
| --- | --- |
| PHP 8.2+ dan CodeIgniter 4.7.6 | MVC, routing eksplisit, validasi, session, CSRF, dan filter peran |
| MariaDB atau MySQL melalui MySQLi | Tabel akun, kategori, produk, dan gambar dengan InnoDB. Pengujian lokal memakai MariaDB 10.11.16 di XAMPP. |
| Bootstrap 5.3.9 lokal dan JavaScript | Layout toko dan dashboard yang responsif serta pratinjau file pada form produk |
| PHP GD dan Fileinfo | Pembacaan MIME, decoding, dan enkode ulang JPEG, PNG, serta WebP |
| Composer, PHPUnit, dan Chrome headless | Instalasi dependensi, pengujian, dan dokumentasi tampilan |

- **Toko publik** menyediakan beranda, katalog dengan filter kategori, detail harga dan stok, serta halaman Tentang.
- **Autentikasi** menyediakan registrasi Customer, login, logout, dan dashboard Administrator, Operator, serta Customer.
- **Product Management** menyediakan pengelolaan kategori, pencarian nama produk, filter kategori dan status, paginasi, serta pengelolaan harga, stok, status, dan gambar sesuai izin.

## Arsitektur MVC

[Routes.php](app/Config/Routes.php) menentukan handler dan filter tiap rute. Controller menangani request, validasi, dan pemilihan View. Model memakai Query Builder CI4 untuk membaca dan menulis tabel. Layanan bawaan CodeIgniter menyediakan session, validasi, dan respons. Kelas pengelolaan gambar mengoordinasikan transaksi dan filesystem.

```mermaid
flowchart TD
    B["Browser"] --> R["Routes.php · GET dan POST eksplisit"]
    R --> F["Filter CI4 · CSRF pada POST dan AuthFilter untuk rute privat"]
    F --> A["Controller beranda, katalog, autentikasi, dashboard, kategori"]
    F --> P["Admin ProductController · grup Admin dan Operator"]
    F --> I["ProductImageController · penyajian publik"]
    F -->|AuthFilter membaca UserModel| M["UserModel, CategoryModel, ProductModel, ProductImageModel"]
    A --> M
    P --> M
    P --> G["ProductImageManager · transaksi dan cleanup"]
    G --> M
    G --> S["ProductImageStorage"]
    I --> M
    I --> S
    S --> W["writable/uploads/products"]
    M --> D[("MariaDB atau MySQL")]
    A --> V["Views dan layout Bootstrap"]
    P --> V
    V -->|HTML| B
    I -->|Berkas gambar| B
    A -.-> CI["Services CI4 · session, validasi, respons"]
    P -.-> CI
    F -.-> CI
```

[Sumber diagram arsitektur mvc](docs/diagrams/arsitektur-mvc.mmd)

| Komponen | Tanggung jawab |
| --- | --- |
| [Controller aplikasi](app/Controllers/) | `HomeController` dan `ProductController` melayani toko. `AuthController` dan `DashboardController` mengelola akun dan dashboard. |
| [Controller pengelolaan katalog](app/Controllers/Admin/) | `CategoryController` dan `ProductController` dipakai bersama oleh Administrator dan Operator. `CatalogController` menyiapkan konteks layout sesuai peran. |
| [Model](app/Models/) | `UserModel`, `CategoryModel`, `ProductModel`, dan `ProductImageModel` memetakan tabel dan menyediakan kueri. |
| [View dan layout](app/Views/) | Halaman toko, autentikasi, dashboard, form, dan galeri memakai layout bersama. |
| [Kelas pengelolaan gambar](app/Libraries/) | `ProductImageManager` mengoordinasikan transaksi. `ProductImageStorage` mengolah dan menyimpan file. |
| [ProductImageController](app/Controllers/ProductImageController.php) | Menyajikan gambar upload melalui GET setelah memeriksa format path dan referensi database. |

## Database

```mermaid
erDiagram
    categories ||--o{ products : mengelompokkan
    products ||--o{ product_images : memiliki

    users {
        int id_user PK
        varchar name
        varchar email UK
        varchar password "hash"
        enum role
        varchar phone
        text address
        datetime created_at
        datetime updated_at
    }
    categories {
        int id_category PK
        varchar name
        varchar slug UK
        text description
        datetime created_at
        datetime updated_at
    }
    products {
        int id_product PK
        int id_category FK
        varchar name
        varchar slug UK
        text description
        decimal price "DECIMAL(12,2)"
        int stock "unsigned"
        enum status
        datetime created_at
        datetime updated_at
    }
    product_images {
        int id_image PK
        int id_product FK
        varchar image "path relatif"
        tinyint is_primary
        datetime created_at
    }
```

[Sumber diagram erd database](docs/diagrams/erd-database.mmd)

| Tabel | Kunci dan kebijakan |
| --- | --- |
| `users` | PK `id_user`, email unik, hash pada kolom `password`, serta peran `customer`, `admin`, atau `operator`. Default peran adalah Customer. |
| `categories` | PK `id_category` dan slug unik. Satu kategori dapat memiliki banyak produk. |
| `products` | PK `id_product`, slug unik, serta FK `id_category`. `ON DELETE RESTRICT` melindungi kategori yang masih memiliki produk. |
| `product_images` | PK `id_image` dan FK `id_product`. `ON DELETE CASCADE` menghapus record gambar ketika produk dihapus. |

Tabel `users` berdiri sendiri karena akun tidak memiliki foreign key ke katalog. Harga disimpan sebagai `DECIMAL(12,2)`, stok sebagai integer unsigned, dan status produk sebagai `tersedia` atau `tidak_tersedia`. [Migration](app/Database/Migrations/) membuat empat tabel. [CatalogSeeder](app/Database/Seeds/CatalogSeeder.php) mengisi **4 kategori, 6 produk, dan 6 gambar utama** pada database kosong.

## Autentikasi dan authorization

Registrasi publik selalu menetapkan peran Customer. Aplikasi memakai `password_hash()` untuk menyimpan password dan `password_verify()` untuk login. Login meregenerasi ID session. Logout memakai POST, mengakhiri session, dan menghapus cookie session. Customer diarahkan ke beranda setelah login dan dapat membuka dashboard melalui navbar.

```mermaid
sequenceDiagram
    actor B as Pengguna
    participant R as Routes dan filter CSRF
    participant A as AuthController
    participant U as UserModel
    participant S as Session CI4
    participant F as AuthFilter
    participant D as DashboardController

    B->>R: GET /login
    R->>A: Tampilkan formulir
    A-->>B: View Login
    B->>R: POST /login dengan kredensial dan token CSRF
    alt Token CSRF gagal
        R-->>B: HTTP 403
    else Token CSRF valid
        R->>A: authenticate
        A->>A: Validasi email dan password
        opt Input valid
            A->>U: Cari akun berdasarkan email
            U-->>A: Akun beserta hash dan peran
        end
        A->>A: password_verify
        alt Kredensial salah
            A-->>B: HTTP 401 dan pesan generik
        else Kredensial valid
            A->>S: Regenerasi ID dan simpan data akun
            A-->>B: HTTP 303 ke /admin, /operator, atau /
        end
    end

    B->>R: GET dashboard sesuai peran
    R->>F: Periksa session dan izin rute
    opt Session dan izin rute valid
        F->>U: Baca peran akun terkini
        U-->>F: Peran dari database
    end
    alt Session sah dan peran cocok
        F->>D: Izinkan rute dashboard
        D-->>B: View dashboard
    else Akun hilang, peran berubah, atau belum login
        F-->>B: Arahkan ke login
    else Peran ditolak pada rute
        F-->>B: HTTP 403
    end
```

[Sumber diagram alur autentikasi](docs/diagrams/alur-autentikasi.mmd)

[AuthFilter](app/Filters/AuthFilter.php) membandingkan peran session dengan izin rute dan peran terkini di MySQL. Session yang sudah kehilangan izin karena akun hilang atau perannya berubah dialihkan ke login pada pemeriksaan rute terlindungi. Peran yang berbeda pada rute terdaftar mendapat HTTP 403. Pengunjung anonim mengikuti pengalihan ke login setelah pemeriksaan CSRF untuk POST. Aksi yang tidak didaftarkan di grup Operator mengembalikan 404.

| Operasi | Administrator | Operator | Customer | Anonim |
| --- | --- | --- | --- | --- |
| Beranda, katalog, filter, dan detail produk | Ya | Ya | Ya | Ya |
| Dashboard | `/admin` | `/operator` | `/customer` | Ke login |
| Lihat kategori dan jumlah produk | Ya | Ya | — | — |
| Tambah, edit, dan hapus kategori | Ya | — | — | — |
| Daftar, detail, tambah, dan edit produk | Ya | Ya | — | — |
| Ubah harga, stok, dan status produk | Ya | Ya | — | — |
| Upload dan pilih gambar utama | Ya | Ya | — | — |
| Hapus produk atau gambar | Ya | — | — | — |

HOL Product Management menempatkan Administrator dan Operator pada modul katalog sesuai izin. Pertemuan 12 menerapkan izin per aksi. `/admin/...` khusus Administrator dan `/operator/...` khusus Operator.

## Product Management

- **Kategori** menampilkan jumlah produk. Nama wajib diisi dan slug unik dibuat dari nama. Penghapusan kategori yang masih digunakan ditolak oleh controller dan FK `RESTRICT`.
- **Produk** dapat dicari dengan `q`, difilter dengan `category` dan `status`, serta dipaginasi 12 item per halaman. Form memvalidasi kategori, harga desimal, stok bulat nonnegatif, dan status. Slug unik dibuat saat tambah dan dipertahankan saat edit. Stok nol dapat disimpan dan ditampilkan sebagai **Stok habis** di detail.
- **Galeri** menerima JPEG, PNG, dan WebP hingga **4 gambar per produk**, **2 MB per file**, dan **4096 × 4096 piksel**. Produk boleh disimpan tanpa gambar. Gambar pertama menjadi utama dan pilihan itu dapat diubah. Jika gambar utama dihapus, gambar tersisa ber-ID terkecil menjadi pengganti. Halaman publik memakai **Foto belum tersedia** jika gambar utama tidak ada atau filenya hilang.
- **Penyimpanan dan cleanup** memakai `writable/uploads/products/` dengan nama acak dan path relatif. Aset bawaan tetap berada di `public/images/shopease/`. Setelah perubahan database berhasil, file upload bebas referensi dibersihkan. Kegagalan cleanup dicatat di log. Perintah `php spark products:cleanup-image <path-relatif>` dapat mengulang cleanup satu path valid setelah lima menit dan memeriksa referensinya.

```mermaid
flowchart TD
    A["Dashboard Administrator"] --> AA["CRUD kategori dan produk · kelola semua aksi gambar"]
    O["Dashboard Operator"] --> OO["Baca kategori · lihat, tambah, edit produk · upload dan pilih utama"]
    AA --> F["Rute sesuai peran · AuthFilter dan CSRF untuk POST"]
    OO --> F
    F --> C["CategoryController"]
    F --> P["Admin ProductController"]
    C --> CM["CategoryModel"]
    CM --> D[("MariaDB atau MySQL")]
    P --> PM["ProductModel · daftar, detail, filter"]
    P -->|Teruskan aksi setelah validasi input| T["ProductImageManager · transaksi dan kunci baris produk"]
    T --> PM
    T --> I["ProductImageModel · relasi dan pilihan gambar utama"]
    T --> S["ProductImageStorage · encode ulang dan simpan upload"]
    PM --> D
    I --> D
    T --> X["Gambar pertama jadi utama · jika dihapus, pilih ID terkecil yang tersisa"]
    D --> K["Beranda, katalog, filter kategori, detail publik"]
    D --> R["ProductImageController · periksa referensi file"]
    S --> R
    R --> K
```

[Sumber diagram alur product management](docs/diagrams/alur-product-management.mmd)

### Infografis Product Management

HOL Product Management bagian Q menetapkan tujuh komponen penilaian. Infografis berikut mempertahankan nama dan bobot komponen, dengan contoh dari implementasi Pertemuan 12.

![Infografis tujuh komponen rubrik HOL Product Management dan penerapannya di ShopEase](docs/infographics/product-management.svg)

## Rute

| Area | Metode dan rute penting |
| --- | --- |
| Publik | `GET /`, `GET /about`, `GET /products`, `GET /products/(:num)`, `GET /product-images/(:segment)` |
| Autentikasi | `GET/POST /login`, `GET/POST /register`, `POST /logout` |
| Dashboard | `GET /admin`, `GET /operator`, `GET /customer` dengan filter peran masing-masing |
| Baca kategori | `GET /admin/categories`, `GET /operator/categories` |
| Kelola kategori Administrator | `GET /admin/categories/create`, `GET /admin/categories/(:num)/edit`, `POST /admin/categories`, `POST /admin/categories/(:num)/update`, `POST /admin/categories/(:num)/delete` |
| Produk Administrator dan Operator | Pada `/admin/products` dan `/operator/products` tersedia GET daftar, `/create`, `/(:num)`, dan `/(:num)/edit`. POST tersedia pada rute daftar dan `/(:num)/update`. |
| Gambar Administrator dan Operator | `POST /admin/products/(:num)/images/(:num)/primary`, `POST /operator/products/(:num)/images/(:num)/primary` |
| Hapus produk dan gambar | `POST /admin/products/(:num)/delete`, `POST /admin/products/(:num)/images/(:num)/delete` |

Auto Routing nonaktif. Semua POST memakai filter CSRF. Daftar lengkap dapat ditampilkan dengan `php spark routes`.

## Instalasi dan menjalankan aplikasi

1. Siapkan PHP 8.2 atau lebih baru, Composer 2, dan MySQL atau MariaDB. Aktifkan `intl`, `mbstring`, `mysqli`, `fileinfo`, serta `gd` dengan dukungan JPEG, PNG, dan WebP. PHPUnit lokal memakai `sqlite3`.
2. Aktifkan modul MySQL di XAMPP dan buat database **`db_shopease_p12`** yang kosong. Contoh memakai port database **3307** dan HTTP **8080**. Sesuaikan host, port, dan akun dengan instalasi sendiri.
3. Buka folder `Pertemuan 12`. Pasang dependensi dan buat `.env` dari [template env](env) jika file lokalnya belum ada.

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item env .env }
```

Contoh pengaturan lokal. Ganti semua nilai di dalam tanda sudut dengan nilai milik sendiri.

```dotenv
app.baseURL = 'http://localhost:8080/'
database.default.hostname = 127.0.0.1
database.default.database = db_shopease_p12
database.default.DBDriver = MySQLi
database.default.port = 3307
database.default.username = '<akun-database-lokal>'
database.default.password = '<password-database-lokal>'

SHOPEASE_DEMO_CUSTOMER_PASSWORD = '<password-demo-customer>'
SHOPEASE_DEMO_ADMIN_PASSWORD = '<password-demo-admin>'
SHOPEASE_DEMO_OPERATOR_PASSWORD = '<password-demo-operator>'
```

Untuk upload, atur `upload_max_filesize` minimal `2M`, `post_max_size` minimal `10M`, dan `max_file_uploads` minimal `5`. Proses PHP perlu akses tulis ke `writable/`.

4. Jalankan migration pada database kosong, isi data awal, buat akun demo, dan jalankan server.

```powershell
php spark migrate
php spark migrate:status
php spark db:seed CatalogSeeder
php spark db:seed DemoUserSeeder
php spark serve --host localhost --port 8080
```

Buka [ShopEase](http://localhost:8080/) atau [Login](http://localhost:8080/login). Akun demo memakai alamat `admin.demo@shopease.test`, `operator.demo@shopease.test`, dan `customer.demo@shopease.test` dengan password yang Anda atur sendiri. `DemoUserSeeder` mempertahankan akun demo yang sudah ada. Jalankan `php spark db:seed DemoUserPasswordSeeder` untuk memperbarui hash ketiga akun setelah mengganti nilai password demo.

Untuk akses dari HP, sambungkan PC dan HP ke jaringan yang sama. Atur `app.baseURL` ke URL IP Wi-Fi PC dengan port yang dipilih, lalu jalankan `php spark serve --host <ip-wifi-pc> --port 8080`. Buka URL tersebut dari HP. Gunakan port alternatif secara konsisten pada perintah dan base URL jika port 8080 sudah dipakai.

## Panduan demo untuk dosen

1. Buka beranda, lalu katalog, filter **Olahraga**, dan detail. Tunjukkan harga, stok, dan gambar utama.
2. Login sebagai Administrator. Buka dashboard dan tunjukkan metrik kategori, produk, dan stok.
3. Tambah kategori demo, misalnya **Kategori Uji PBKK**, lalu edit namanya. Tambahkan produk percobaan pada kategori tersebut, tampilkan di katalog, lalu ubah harga, stok, dan status.
4. Upload dua gambar demo dan ganti gambar utama dari galeri. Buka detail publik untuk menunjukkan hasilnya.
5. Hapus satu gambar percobaan, lalu hapus produk demo. Setelah kategori demo kosong, hapus kategori demo dan logout. Gunakan database praktikum atau fixture serta data yang dibuat khusus untuk demonstrasi.
6. Login sebagai Operator. Buka daftar kategori dan produk. Buat atau ubah produk percobaan pada kategori demo lain yang sudah disiapkan. Tunjukkan upload dan pemilihan gambar utama. Daftar produk Operator menampilkan aksi Detail dan Edit. Logout setelah selesai.
7. Login sebagai Customer. Buka dashboard Customer dari navbar, kembali ke katalog, dan tunjukkan detail produk. Jika menyisakan produk demo dari langkah Operator, gunakan Administrator untuk membersihkan data demo tersebut.

## Screenshot aplikasi

Chrome headless mengambil **19 tampilan desktop** dari viewport 1366 × 768 dan **3 tampilan mobile** dari viewport 390 × 844. Beberapa halaman diambil penuh agar seluruh form terbaca. Akun dan data memakai seeder di tabel sementara. Galeri memakai satu foto bawaan dan satu unggahan detail dari foto yang sama melalui fitur edit produk.

<details>
<summary>Halaman publik (4)</summary>

#### Beranda desktop

Hero dan navigasi awal menuju katalog.

![Beranda desktop ShopEase](docs/screenshots/homepage-desktop.webp)

#### Katalog produk

Enam produk dari data seeder dengan harga dan tautan detail.

![Katalog produk ShopEase](docs/screenshots/catalog-desktop.webp)

#### Filter kategori Olahraga

Kategori Olahraga aktif dan daftar produk yang cocok.

![Filter kategori Olahraga ShopEase](docs/screenshots/catalog-filter-olahraga.webp)

#### Detail produk publik

Wireless Headphone dengan gambar utama, deskripsi, harga, status, dan stok.

![Detail produk publik ShopEase](docs/screenshots/product-detail.webp)

</details>

<details>
<summary>Autentikasi (2)</summary>

#### Login

Form masuk untuk ketiga peran.

![Login ShopEase](docs/screenshots/login.webp)

#### Registrasi

Form publik untuk membuat akun Customer.

![Registrasi ShopEase](docs/screenshots/register.webp)

</details>

<details>
<summary>Administrator (9)</summary>

#### Dashboard Administrator

Ringkasan kategori, produk, status, dan stok nol.

![Dashboard Administrator ShopEase](docs/screenshots/admin-dashboard.webp)

#### Daftar kategori

Jumlah produk per kategori dan akses untuk mengubah kategori.

![Daftar kategori ShopEase](docs/screenshots/admin-categories.webp)

#### Tambah kategori

Form nama dan deskripsi kategori.

![Tambah kategori ShopEase](docs/screenshots/admin-category-create.webp)

#### Edit kategori

Form edit kategori Olahraga dengan nilai yang tersimpan.

![Edit kategori ShopEase](docs/screenshots/admin-category-edit.webp)

#### Daftar produk Administrator

Pencarian, filter, thumbnail, dan aksi CRUD produk.

![Daftar produk Administrator ShopEase](docs/screenshots/admin-products.webp)

#### Tambah produk

Form produk dengan input gambar opsional.

![Tambah produk ShopEase](docs/screenshots/admin-product-create.webp)

#### Edit produk Administrator

Form dengan harga, stok, status, dan upload tambahan.

![Edit produk Administrator ShopEase](docs/screenshots/admin-product-edit.webp)

#### Detail produk Administrator

Rincian produk, slug, dan foto utama di dashboard.

![Detail produk Administrator ShopEase](docs/screenshots/admin-product-detail.webp)

#### Galeri gambar produk

Dua gambar pada fixture, penanda Utama, dan aksi galeri Administrator.

![Galeri gambar produk ShopEase](docs/screenshots/admin-product-gallery.webp)

</details>

<details>
<summary>Operator (3)</summary>

#### Dashboard Operator

Ringkasan stok dengan navigasi Kategori dan Produk Operator.

![Dashboard Operator ShopEase](docs/screenshots/operator-dashboard.webp)

#### Daftar produk Operator

Aksi Detail dan Edit sesuai izin Operator.

![Daftar produk Operator ShopEase](docs/screenshots/operator-products.webp)

#### Form pengelolaan produk Operator

Edit produk, upload, dan pilih utama sesuai izin Operator.

![Form pengelolaan produk Operator ShopEase](docs/screenshots/operator-product-edit.webp)

</details>

<details>
<summary>Customer (1)</summary>

#### Dashboard Customer

Dashboard akun demo dengan akses kembali ke toko.

![Dashboard Customer ShopEase](docs/screenshots/customer-dashboard.webp)

</details>

<details>
<summary>Tampilan mobile (3)</summary>

#### Beranda mobile

Hero tersusun vertikal pada lebar 390 piksel.

![Beranda mobile ShopEase](docs/screenshots/homepage-mobile.webp)

#### Katalog mobile

Navigasi dan kartu produk pada layar sempit.

![Katalog mobile ShopEase](docs/screenshots/catalog-mobile.webp)

#### Form Operator mobile

Form edit dan galeri Operator pada lebar 390 piksel.

![Form Operator mobile ShopEase](docs/screenshots/operator-product-edit-mobile.webp)

</details>

## Pengujian dan keamanan

Perintah pemeriksaan yang dijalankan pada PHP 8.5.12 dan PHPUnit 10.5.67:

```powershell
composer check-platform-reqs
Get-ChildItem -LiteralPath app -Filter *.php -File -Recurse | ForEach-Object { php -l $_.FullName }
node --check public/js/admin-product-images.js
php spark routes
php -d extension=sqlite3 vendor/bin/phpunit --no-coverage --do-not-cache-result --no-logging
```

| Pemeriksaan | Hasil |
| --- | --- |
| Platform, lint, dan route | Lulus. Lint mencakup 102 file PHP aplikasi. |
| PHPUnit | **7 tes, 25 asersi**, termasuk [uji kontrak rute peran](tests/unit/CatalogRoutesTest.php). |
| Autentikasi dan role | Registrasi, email duplikat, password tidak valid, login salah dan benar, regenerasi session, logout, CSRF, dan empat kondisi akses telah diuji. Session Operator lama kehilangan akses setelah peran akun diturunkan pada fixture. |
| Katalog dan CRUD | Kategori yang dirujuk produk terlindungi oleh FK. CRUD produk, slug unik, pencarian literal, filter, paginasi, angka harga dan stok, status, serta perubahan di halaman publik lulus pengujian. |
| Gambar | Upload tiga format, penolakan file palsu atau berbahaya, dimensi dan ukuran berlebih, set utama, validasi kepemilikan, cleanup, akses file, rollback, serta operasi bersamaan lulus pengujian. |
| Tampilan | Chrome headless menguji 390, 768, dan 1280 piksel tanpa overflow halaman atau gambar rusak pada skenario Stage 10.1. Stage 10.2 memeriksa ulang halaman dan aset sebelum mengambil screenshot. |

Aplikasi memakai Query Builder dengan parameter dan escaping output dinamis. Server memeriksa MIME aktual, ekstensi, dimensi, dan hasil decoding upload. File dienkode ulang, disimpan di luar document root, dan hanya disajikan lewat rute jika masih dirujuk. Uji Stage 10.1 memakai fixture MariaDB dan upload sementara, termasuk kegagalan insert terkontrol untuk memeriksa rollback dan respons produksi generik.

Pada Stage 10.2, keempat diagram dirender dengan Mermaid. Semua tautan relatif, 22 screenshot, dan SVG infografis lulus pemeriksaan lokal. Sebelum dan sesudah pengambilan screenshot, hash data permanen sama dengan 4 kategori, 6 produk, 6 record gambar, dan 3 akun.

## Batasan dan kesiapan pengumpulan

- Aplikasi untuk pembelajaran. Keranjang, checkout, pembayaran, pengiriman, pesanan, dan laporan penjualan berada di luar cakupan.
- Kewenangan Operator mengikuti matriks per aksi. Transaksi aplikasi menjaga satu gambar utama per produk. Skema belum memiliki constraint SQL khusus untuk aturan tersebut.
- Pengujian mencakup lingkungan lokal. `php spark serve` digunakan untuk pengembangan dan demonstrasi.
- Sertakan source, migration, seeder, tes, README, serta aset di `docs/`. Git mengabaikan `.env`, `vendor/`, log, session, cache, dan upload lokal. Siapkan kredensial sendiri pada mesin yang akan dipakai untuk demo.

## Referensi praktikum

- `Studi_Kasus_ShopEase_CodeIgniter4.docx` dan `PRD_ShopEase_CodeIgniter4.docx`.
- `HOL_ShopEase_CodeIgniter4.docx`, bagian MVC, katalog, database, dan autentikasi.
- `Hands-On_Lab_Authentication_System_CodeIgniter_4.docx` dan `Tahapan_Implementasi_Authentication_ShopEase_CI4.docx`.
- `HOL_Product_Management_ShopEase_CI4.docx`, termasuk rubrik bagian Q dan catatan teknis.
- `12_UI_Product_Management.png` sebagai referensi struktur dashboard, form, dan galeri.

## Kesimpulan

Pertemuan 12 menghubungkan katalog publik dengan manajemen kategori, produk, dan gambar yang memakai database. Autentikasi dan izin peran memberi Administrator, Operator, serta Customer alur kerja yang sesuai cakupan praktikum.
