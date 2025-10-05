# Form Post Plugin - WordPress Webinar Registration

Plugin WordPress untuk formulir pendaftaran webinar dengan database dan manajemen admin.

## Fitur Utama

### 1. Formulir Pendaftaran Webinar

- Field formulir:
  - Nama Lengkap
  - Email
  - Nomor Telepon
  - Instansi/Perusahaan
  - Jabatan
  - Alamat
  - Keterangan/Tambahan (opsional)
- Validasi formulir di frontend dan backend
- Captcha untuk mencegah spam

### 2. Manajemen Data Pendaftar

- Dashboard admin untuk melihat semua pendaftar
- Status pendaftar: Diterima/Ditolak/Pending
- Filter dan search functionality
- Detail view untuk setiap pendaftar
- Bulk actions (update status, delete, export)

### 3. Database

- Tabel khusus untuk menyimpan data pendaftar webinar
- Struktur tabel yang efisien dengan indeks yang tepat

### 4. REST API

- Endpoint untuk submit formulir
- Endpoint untuk mengambil data pendaftar
- Endpoint untuk update status pendaftar

### 5. Frontend

- Shortcode untuk menampilkan formulir: `[webinar_registration_form]`
- Responsive design
- Alpine JS untuk interactivity
- Tanpa AJAX, menggunakan REST API dan Alpine JS

### 6. Export Data

- Export data pendaftar ke CSV/Excel
- Filter berdasarkan status dan tanggal

### 7. Notifikasi Email

- Email notifikasi ke admin saat ada pendaftar baru
- Email konfirmasi ke pendaftar
- Email template dapat dikustomisasi

### 8. Pengaturan Plugin

- Halaman pengaturan untuk konfigurasi:
  - Email admin
  - Email template
  - Pengaturan captcha
  - Pengaturan formulir

## Instalasi

1. Upload folder `form-post` ke `/wp-content/plugins/`
2. Aktifkan plugin melalui dashboard WordPress
3. Konfigurasi pengaturan plugin di menu "Webinar Registration" → "Settings"

## Penggunaan

### Menampilkan Formulir

Tambahkan shortcode `[webinar_registration_form]` di halaman atau post mana saja

### Parameter Shortcode

- `title` - Judul formulir (default: "Webinar Registration")
- `description` - Deskripsi formulir (default: "Please fill in the form below to register for our webinar.")
- `show_captcha` - Tampilkan/sembunyikan captcha (default: "true")

### Contoh Penggunaan

```php
// Basic usage
[webinar_registration_form]

// Dengan judul kustom
[webinar_registration_form title="Register for Our Webinar"]

// Dengan deskripsi kustom
[webinar_registration_form description="Join us for an informative webinar session."]

// Menyembunyikan captcha
[webinar_registration_form show_captcha="false"]
```

### Mengelola Pendaftar

1. Buka menu "Webinar Registration" di dashboard admin
2. Lihat semua data pendaftar
3. Update status (Diterima/Ditolak)
4. Export data jika needed

## Struktur Plugin

```
form-post/
├── form-post.php              # File utama plugin
├── includes/
│   ├── class-form-post-init.php
│   ├── class-database.php
│   ├── class-admin.php
│   ├── class-frontend.php
│   ├── class-rest-api.php
│   ├── class-settings.php
│   └── partials/
│       ├── webinar-registration-admin-display.php
│       ├── webinar-registration-registrations-display.php
│       └── webinar-registration-settings-display.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
├── languages/
│   └── form-post-id_ID.po
├── features.md
├── database-design.md
└── README.md
```

## Database Schema

### wp_webinar_registrations

- `id` - Primary key
- `nama_lengkap` - Nama lengkap pendaftar
- `email` - Email pendaftar (unique)
- `nomor_telepon` - Nomor telepon
- `instansi` - Instansi/perusahaan
- `jabatan` - Jabatan
- `alamat` - Alamat
- `keterangan` - Keterangan tambahan
- `status` - Status (pending/diterima/ditolak)
- `ip_address` - IP address pendaftar
- `user_agent` - Browser info
- `created_at` - Tanggal pendaftaran
- `updated_at` - Tanggal update

### wp_webinar_settings

- `id` - Primary key
- `setting_key` - Key pengaturan
- `setting_value` - Value pengaturan
- `created_at` - Tanggal dibuat
- `updated_at` - Tanggal update

## REST API Endpoints

### Submit Formulir

- **Endpoint:** `POST /wp-json/form-post/v1/submit`
- **Parameters:**
  - `nama_lengkap` (required)
  - `email` (required)
  - `nomor_telepon` (required)
  - `instansi` (optional)
  - `jabatan` (optional)
  - `alamat` (optional)
  - `keterangan` (optional)
  - `g-recaptcha-response` (optional, jika captcha diaktifkan)

### Get Registrations (Admin Only)

- **Endpoint:** `GET /wp-json/form-post/v1/registrations`
- **Parameters:**
  - `status` (optional) - Filter berdasarkan status
  - `limit` (optional) - Jumlah data per halaman
  - `offset` (optional) - Offset untuk pagination

### Update Registration Status (Admin Only)

- **Endpoint:** `PUT /wp-json/form-post/v1/registrations/{id}`
- **Parameters:**
  - `status` (required) - Status baru (pending/diterima/ditolak)

### Delete Registration (Admin Only)

- **Endpoint:** `DELETE /wp-json/form-post/v1/registrations/{id}`

### Get Statistics (Admin Only)

- **Endpoint:** `GET /wp-json/form-post/v1/statistics`

## Teknologi yang Digunakan

- WordPress Plugin API
- REST API WordPress
- Alpine JS untuk frontend interactivity
- WordPress Database API (wpdb)
- WordPress Settings API
- WordPress Mail Function

## Keamanan

- Input sanitization dan validation
- CSRF protection dengan WordPress nonces
- SQL injection prevention dengan prepared statements
- Captcha integration untuk mencegah spam
- Permission checks untuk admin endpoints

## Compatibility

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

## Support

Dibuat oleh: Websweetstudio.com - Aditya Kristyanto

Untuk dukungan dan dokumentasi, kunjungi: https://websweetstudio.com

## Changelog

### 1.0.0

- Initial release
- Formulir pendaftaran webinar
- Manajemen data pendaftar
- Export CSV
- Notifikasi email
- Captcha integration
- REST API endpoints
