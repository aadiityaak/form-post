# Form Post Plugin - WordPress Webinar Registration

## Plugin Information

- **Name**: Form Post
- **Author**: Websweetstudio.com - Aditya Kristyanto
- **Description**: Plugin WordPress untuk formulir pendaftaran webinar dengan database dan manajemen admin

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

## Struktur Folder Plugin

```
form-post/
├── form-post.php              # File utama plugin
├── includes/
│   ├── class-form-post-init.php
│   ├── class-database.php
│   ├── class-admin.php
│   ├── class-frontend.php
│   ├── class-rest-api.php
│   └── class-settings.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
├── templates/
│   ├── form-template.php
│   └── admin-template.php
└── languages/
    └── form-post-id_ID.po
```

## Cara Penggunaan

### Instalasi

1. Upload folder `form-post` ke `/wp-content/plugins/`
2. Aktifkan plugin melalui dashboard WordPress

### Menampilkan Formulir

Tambahkan shortcode `[webinar_registration_form]` di halaman atau post mana saja

### Mengelola Pendaftar

1. Buka menu "Webinar Registration" di dashboard admin
2. Lihat semua data pendaftar
3. Update status (Diterima/Ditolak)
4. Export data jika needed

## Teknologi yang Digunakan

- WordPress Plugin API
- REST API WordPress
- Alpine JS untuk frontend interactivity
- WordPress Database API (wpdb)
- WordPress Settings API
- WordPress Mail Function
