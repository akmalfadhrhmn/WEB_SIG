# Quick Setup Guide

## Langkah-langkah Setup Cepat

### 0. Copy Project ke Laragon

**Lokasi Project Awal:** `D:\PROJEK_SIG`  
**Lokasi Project di Laragon:** `C:\laragon\www\PROJEK_SIG`

**Cara Copy Project:**

1. **Menggunakan File Explorer (Paling Mudah):**
   - Buka File Explorer
   - Navigate ke `D:\PROJEK_SIG`
   - Copy seluruh folder `PROJEK_SIG` (Ctrl+C)
   - Paste ke `C:\laragon\www\` (Ctrl+V)

2. **Menggunakan Command Prompt:**
   ```bash
   # Buka Command Prompt (Win + R, ketik cmd)
   xcopy D:\PROJEK_SIG C:\laragon\www\PROJEK_SIG /E /I /Y
   ```
   - `/E` = Copy semua subfolder termasuk yang kosong
   - `/I` = Asumsikan sebagai folder jika tidak ada
   - `/Y` = Overwrite tanpa konfirmasi

3. **Verifikasi:**
   - Pastikan folder `C:\laragon\www\PROJEK_SIG` sudah ada
   - Pastikan semua file dan folder sudah ter-copy (api, assets, config, database, data, dll)

### 1. Setup Database (Laragon + HeidiSQL)

**Lokasi Project:** `C:\laragon\www\PROJEK_SIG`

**Opsi 1: Menggunakan HeidiSQL (Recommended)**

1. **Buka HeidiSQL:**
   - Klik tombol "Database" di Laragon untuk membuka HeidiSQL
   - Atau buka HeidiSQL secara manual
   - Connect ke MySQL Laragon (Host: `127.0.0.1`, User: `root`, Password: kosong)

2. **Import Schema:**
   - Di HeidiSQL, klik menu **File** → **Load SQL file...**
   - Pilih file: `C:\laragon\www\PROJEK_SIG\database\schema.sql`
   - Klik **Execute** (F9) atau tombol play ▶️
   - Database `webgis_pendidikan` akan otomatis dibuat beserta 3 tabel

3. **Verifikasi:**
   - Refresh database list di HeidiSQL (F5)
   - Pastikan database `webgis_pendidikan` muncul
   - Klik database tersebut, pastikan ada 3 tabel: `kecamatan`, `kecamatan_analisis`, `sekolah`

**Opsi 2: Menggunakan Terminal Laragon**
```bash
# Di terminal Laragon
cd C:\laragon\www\PROJEK_SIG
mysql -u root < database\schema.sql
```

### 2. Konfigurasi Database

Edit file `C:\laragon\www\PROJEK_SIG\config\database.php`:
```php
$servername = "localhost";
$username = "root";        // Default Laragon
$password = "";            // Kosong (default Laragon)
$dbname = "webgis_pendidikan";
```

### 3. Import Data GeoJSON (Menggunakan Terminal Laragon)

**Lokasi Project:** `C:\laragon\www\PROJEK_SIG`

**Cara Menggunakan Terminal Laragon:**

1. **Buka Terminal Laragon:**
   - Klik kanan pada icon Laragon di system tray
   - Pilih **Terminal** atau **Terminal Here**
   - Atau klik tombol **Terminal** di Laragon

2. **Masuk ke folder project:**
   ```bash
   cd C:\laragon\www\PROJEK_SIG
   ```
   
   **Catatan:** Terminal Laragon otomatis berada di `C:\laragon\www`, jadi bisa langsung:
   ```bash
   cd PROJEK_SIG
   ```

3. **Jalankan script import:**
   ```bash
   php database\import_geojson.php
   ```
   
   **Output yang diharapkan:**
   ```
   ========================================
   Import GeoJSON to MySQL
   WebGIS Pendidikan Lampung Selatan
   ========================================
   
   Importing Kecamatan from: C:\laragon\www\PROJEK_SIG\data/administrasikecamatan.geojson
   Imported XX kecamatan records
   
   Importing Kecamatan Analisis from: ...
   Imported XX kecamatan analisis records
   
   Importing Sekolah from: ...
   Imported XX sekolah records
   
   ========================================
   Import completed!
   ========================================
   ```

4. **Jika ada error:**
   - Pastikan MySQL sudah running (hijau di Laragon)
   - Pastikan database sudah dibuat
   - Pastikan file GeoJSON ada di folder `data/`

**Pastikan file GeoJSON ada di folder `data/`:**
- `administrasikecamatan.geojson`
- `digitasipendidikan.geojson`
- `jumlahsekolahkecamatan.geojson`

### 4. Jalankan Aplikasi

#### Menggunakan Laragon (Recommended):

1. **Start Laragon:**
   - Buka aplikasi Laragon
   - Klik "Start All" (Apache & MySQL harus hijau)

2. **Akses Aplikasi:**
   - Buka browser
   - Akses: `http://localhost/PROJEK_SIG`
   - Atau: `http://webgis-pendidikan.test` (jika setup virtual host)

3. **Setup Virtual Host (Opsional):**
   - Buka Laragon → Menu → Tools → Quick add → Virtual Hosts
   - Domain: `webgis-pendidikan.test`
   - Path: `C:\laragon\www\PROJEK_SIG`
   - Restart Laragon
   - Akses: `http://webgis-pendidikan.test`

#### Alternatif: PHP Built-in Server (Terminal Laragon)
```bash
# Di terminal Laragon
cd C:\laragon\www\PROJEK_SIG
php -S localhost:8000
```
Buka browser: `http://localhost:8000`

### 5. Test API Endpoints

Test di browser atau Postman:
- `http://localhost/PROJEK_SIG/api/get_kecamatan.php`
- `http://localhost/PROJEK_SIG/api/get_kecamatan_analisis.php`
- `http://localhost/PROJEK_SIG/api/get_sekolah.php`
- `http://localhost/PROJEK_SIG/api/get_statistik.php`

Atau jika menggunakan PHP built-in server:
- `http://localhost:8000/api/get_kecamatan.php`
- `http://localhost:8000/api/get_kecamatan_analisis.php`
- `http://localhost:8000/api/get_sekolah.php`
- `http://localhost:8000/api/get_statistik.php`

## Refresh Database (Update Data)

Jika Anda mengubah data GeoJSON atau ingin mengimpor ulang data:

### Cara 1: Truncate & Re-import (Terminal Laragon)
```bash
cd C:\laragon\www\PROJEK_SIG

# Hapus semua data (tapi tetap struktur tabel)
mysql -u root -e "USE webgis_pendidikan; TRUNCATE TABLE sekolah; TRUNCATE TABLE kecamatan; TRUNCATE TABLE kecamatan_analisis;"

# Import ulang data
php database\import_geojson.php
```

### Cara 2: Menggunakan HeidiSQL
1. Buka HeidiSQL di Laragon
2. Pilih database `webgis_pendidikan`
3. Klik kanan setiap tabel → **Empty** (untuk menghapus data)
4. Import ulang: Jalankan `php database\import_geojson.php` di terminal

### Cara 3: Drop & Recreate (Hapus Database Lengkap)
```bash
# Hapus database dan buat ulang
mysql -u root -e "DROP DATABASE IF EXISTS webgis_pendidikan;"
mysql -u root < database\schema.sql
php database\import_geojson.php
```

**Catatan:** Setelah refresh database, refresh browser (F5) untuk melihat perubahan.

## Troubleshooting

**Error: "Koneksi gagal"**
- Pastikan MySQL running
- Cek config/database.php

**Error: "ST_GeomFromGeoJSON not found"**
- Pastikan MySQL 5.7+ dengan spatial extension
- Test: `SELECT ST_GeomFromGeoJSON('{"type":"Point","coordinates":[0,0]}');`

**Data tidak muncul**
- Pastikan import script sudah dijalankan
- Cek data di database: `SELECT COUNT(*) FROM sekolah;`
- **Refresh database** jika data sudah diubah (lihat bagian Refresh Database di atas)

**Peta kosong**
- Cek console browser (F12)
- Pastikan API mengembalikan data valid
- Cek Network tab untuk error API

## Struktur File

```
PROJEK_SIG/
├── api/              # Backend API
├── assets/           # CSS & JS
├── config/           # Database config
├── database/         # Schema & import script
├── data/             # GeoJSON files
├── index.php         # Main page
├── README.md         # Full documentation
└── SETUP.md          # This file
```

## Next Steps

1. ✅ Setup database
2. ✅ Import GeoJSON
3. ✅ Test API endpoints
4. ✅ Buka aplikasi di browser
5. ✅ Test semua fitur (filter, search, toggle layer)

Selamat menggunakan WebGIS!

