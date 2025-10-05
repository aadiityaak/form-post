# Database Design - Form Post Plugin

## Tabel Utama: wp_webinar_registrations

Struktur tabel untuk menyimpan data pendaftar webinar:

```sql
CREATE TABLE wp_webinar_registrations (
    id int(11) NOT NULL AUTO_INCREMENT,
    nama_lengkap varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    nomor_telepon varchar(50) NOT NULL,
    instansi varchar(255) DEFAULT NULL,
    jabatan varchar(255) DEFAULT NULL,
    alamat text DEFAULT NULL,
    keterangan text DEFAULT NULL,
    status enum('pending','diterima','ditolak') NOT NULL DEFAULT 'pending',
    ip_address varchar(45) DEFAULT NULL,
    user_agent text DEFAULT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email),
    KEY idx_status (status),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Penjelasan Field:

1. **id**: Primary key, auto increment
2. **nama_lengkap**: Nama lengkap pendaftar (wajib)
3. **email**: Email pendaftar (wajib, unique)
4. **nomor_telepon**: Nomor telepon pendaftar (wajib)
5. **instansi**: Nama instansi/perusahaan (opsional)
6. **jabatan**: Jabatan pendaftar (opsional)
7. **alamat**: Alamat lengkap (opsional)
8. **keterangan**: Keterangan tambahan (opsional)
9. **status**: Status pendaftaran (pending/diterima/ditolak)
10. **ip_address**: IP address pendaftar untuk tracking
11. **user_agent**: Browser info pendaftar
12. **created_at**: Tanggal pendaftaran
13. **updated_at**: Tanggal update data

## Tabel Tambahan: wp_webinar_settings

Struktur tabel untuk menyimpan pengaturan plugin:

```sql
CREATE TABLE wp_webinar_settings (
    id int(11) NOT NULL AUTO_INCREMENT,
    setting_key varchar(255) NOT NULL,
    setting_value longtext DEFAULT NULL,
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Data Default untuk wp_webinar_settings:

```sql
INSERT INTO wp_webinar_settings (setting_key, setting_value) VALUES
('admin_email', 'admin@example.com'),
('email_subject_admin', 'Pendaftaran Webinar Baru'),
('email_subject_user', 'Konfirmasi Pendaftaran Webinar'),
('email_template_admin', 'Ada pendaftaran webinar baru dari {nama_lengkap} ({email})'),
('email_template_user', 'Terima kasih telah mendaftar webinar kami. Kami akan menghubungi Anda segera.'),
('enable_captcha', '1'),
('captcha_site_key', ''),
('captcha_secret_key', '');
```

## Index untuk Performa:

1. **Primary Key**: id (auto increment)
2. **Unique Key**: email (mencegah duplikasi email)
3. **Index**: status (untuk filter berdasarkan status)
4. **Index**: created_at (untuk sorting berdasarkan tanggal)

## Query yang Akan Sering Digunakan:

1. **Ambil semua data dengan status tertentu**:

```sql
SELECT * FROM wp_webinar_registrations WHERE status = 'pending' ORDER BY created_at DESC;
```

2. **Update status pendaftar**:

```sql
UPDATE wp_webinar_registrations SET status = 'diterima' WHERE id = 1;
```

3. **Cari berdasarkan email atau nama**:

```sql
SELECT * FROM wp_webinar_registrations WHERE email LIKE '%example%' OR nama_lengkap LIKE '%example%';
```

4. **Export data dengan filter tanggal**:

```sql
SELECT * FROM wp_webinar_registrations WHERE created_at BETWEEN '2023-01-01' AND '2023-12-31';
```

5. **Statistik pendaftaran**:

```sql
SELECT status, COUNT(*) as total FROM wp_webinar_registrations GROUP BY status;
```

## Keamanan Data:

1. **Prepared Statements**: Gunakan prepared statements untuk mencegah SQL injection
2. **Data Sanitization**: Sanitasi input sebelum menyimpan ke database
3. **Validation**: Validasi data di frontend dan backend
4. **Escaping**: Escape output saat menampilkan data

## Backup & Restore:

1. **Backup**: Export tabel menggunakan WordPress backup plugin atau phpMyAdmin
2. **Restore**: Import tabel menggunakan phpMyAdmin atau WP-CLI
3. **Migration**: Gunakan WordPress export/import untuk migrasi data
