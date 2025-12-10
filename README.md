# WebGIS - Analisis Sebaran Fasilitas Pendidikan

Aplikasi WebGIS interaktif untuk analisis sebaran fasilitas pendidikan di Kabupaten Lampung Selatan.

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+ (Native)
- **Database**: MySQL 5.7+ (dengan Spatial Extension)
- **Frontend**: HTML5, Tailwind CSS, Leaflet.js
- **Data**: GeoJSON dari QGIS

## 📋 Persyaratan

- PHP 7.4+
- MySQL 5.7+ (dengan spatial extension)
- Web server (Apache/Nginx) atau PHP built-in server
- Browser modern

## 🚀 Quick Start

### 1. Clone Repository
```bash
git clone <repository-url>
cd PROJEK_SIG
```

### 2. Setup dengan Laragon (Recommended untuk Windows)

#### Install Laragon
- Download Laragon dari [laragon.org](https://laragon.org/)
- Install Laragon (include Apache, MySQL, PHP, HeidiSQL)
- Start Laragon → Klik "Start All"

#### Copy Project ke Laragon
- Copy folder `PROJEK_SIG` ke `C:\laragon\www\`
- Atau clone langsung ke `C:\laragon\www\PROJEK_SIG`

### 3. Setup Database dengan HeidiSQL

#### Buka HeidiSQL
- Di Laragon, klik tombol **"Database"** (akan buka HeidiSQL)
- Atau buka HeidiSQL manual
- **Connection:** Host `127.0.0.1`, User `root`, Password (kosong/default Laragon)

#### Import Schema Database
**Cara 1: Import File SQL (Paling Mudah)**
1. Di HeidiSQL, klik menu **File** → **Load SQL file...**
2. Pilih file: `C:\laragon\www\PROJEK_SIG\database\schema.sql`
3. Klik **Execute** (F9) atau tombol play ▶️
4. Database `webgis_pendidikan` akan otomatis dibuat

**Cara 2: Copy-Paste Manual**
1. Buka file `database/schema.sql` dengan text editor
2. Copy semua isi (Ctrl+A, Ctrl+C)
3. Di HeidiSQL, paste di Query tab (Ctrl+V)
4. Klik **Execute** (F9)

**Cara 3: Terminal Laragon**
```bash
# Buka Terminal Laragon
cd C:\laragon\www\PROJEK_SIG
mysql -u root < database\schema.sql
```

#### Verifikasi Database
- Di HeidiSQL, refresh database list (F5)
- Pastikan database `webgis_pendidikan` muncul
- Klik database tersebut, pastikan ada 3 tabel:
  - `kecamatan`
  - `kecamatan_analisis`
  - `sekolah`

### 4. Konfigurasi Database

Edit `config/database.php`:
```php
$servername = "localhost";
$username = "root";
$password = "";  // Default Laragon (kosong)
$dbname = "webgis_pendidikan";
```

**Catatan:** Jika MySQL Laragon menggunakan password, sesuaikan `$password`

### 5. Import Data GeoJSON

**Menggunakan Terminal Laragon:**
1. Buka Terminal Laragon (klik kanan icon Laragon → Terminal)
2. Masuk ke folder project:
   ```bash
   cd PROJEK_SIG
   ```
3. Jalankan script import:
   ```bash
   php database\import_geojson.php
   ```

**Pastikan file GeoJSON ada di folder `data/`:**
- `administrasikecamatan.geojson`
- `digitasipendidikan.geojson`
- `jumlahsekolahkecamatan.geojson`

### 6. Jalankan Aplikasi

**Menggunakan Laragon:**
1. Pastikan Apache & MySQL running (hijau di Laragon)
2. Buka browser: `http://localhost/PROJEK_SIG`

**Setup Virtual Host (Opsional):**
1. Laragon → Menu → Tools → Quick add → Virtual Hosts
2. Domain: `webgis-pendidikan.test`
3. Path: `C:\laragon\www\PROJEK_SIG`
4. Restart Laragon
5. Akses: `http://webgis-pendidikan.test`

**Atau menggunakan PHP Built-in Server:**
```bash
cd C:\laragon\www\PROJEK_SIG
php -S localhost:8000
```
Buka browser: `http://localhost:8000`

## 📁 Struktur Project

```
PROJEK_SIG/
├── api/              # Backend API endpoints
├── assets/           # CSS & JavaScript
├── config/           # Konfigurasi database
├── database/         # Schema & import script
├── data/             # Data GeoJSON
└── index.php         # Halaman utama
```

## ✨ Fitur

- ✅ Peta interaktif dengan Leaflet.js
- ✅ Layer batas kecamatan (polygon)
- ✅ Layer analisis choropleth
- ✅ Marker sekolah dengan icon berbeda per jenjang
- ✅ Filter berdasarkan jenjang pendidikan
- ✅ Pencarian sekolah
- ✅ Statistik dashboard
- ✅ **CRUD data titik** (Create, Read, Update, Delete)
- ✅ **Geocoding** (pencarian lokasi)
- ✅ **Tambah marker dari interface** (klik peta)

## 🔌 API Endpoints

### GET Endpoints
- `GET /api/get_kecamatan.php` - Data batas kecamatan
- `GET /api/get_kecamatan_analisis.php` - Hasil analisis
- `GET /api/get_sekolah.php` - Data sekolah (dengan filter)
- `GET /api/get_statistik.php` - Statistik data
- `GET /api/geocode.php?q={query}` - Geocoding

### CRUD Endpoints
- `POST /api/create_sekolah.php` - Tambah sekolah
- `PUT /api/update_sekolah.php?id={id}` - Update sekolah
- `DELETE /api/delete_sekolah.php?id={id}` - Hapus sekolah

## 🗄️ Database Schema

**Tabel:**
- `kecamatan` - Batas administrasi kecamatan
- `kecamatan_analisis` - Hasil analisis jumlah sekolah
- `sekolah` - Titik digitasi sekolah

Semua tabel menggunakan **SRID 4326** (WGS84) untuk geometry.

## 🔄 Refresh Database (Update Data)

Jika Anda mengubah data GeoJSON atau ingin mengimpor ulang data:

### Cara 1: Truncate & Re-import (Recommended)
```bash
# Di Terminal Laragon
cd C:\laragon\www\PROJEK_SIG

# Hapus semua data (tapi tetap struktur tabel)
mysql -u root -e "USE webgis_pendidikan; TRUNCATE TABLE sekolah; TRUNCATE TABLE kecamatan; TRUNCATE TABLE kecamatan_analisis;"

# Import ulang data
php database\import_geojson.php
```

### Cara 2: Menggunakan HeidiSQL
1. Buka HeidiSQL di Laragon
2. Pilih database `webgis_pendidikan`
3. Klik kanan tabel → **Empty** (untuk menghapus data) atau **Drop** (untuk hapus tabel)
4. Jika drop tabel, import ulang schema: `File` → `Load SQL file...` → pilih `database/schema.sql`
5. Import data: Jalankan `php database\import_geojson.php` di terminal

### Cara 3: Drop & Recreate Database
```bash
# Hapus database dan buat ulang
mysql -u root -e "DROP DATABASE IF EXISTS webgis_pendidikan;"
mysql -u root < database\schema.sql
php database\import_geojson.php
```

**Catatan:** Setelah refresh database, refresh browser (F5) untuk melihat perubahan.

## ⚠️ Troubleshooting

### Error Koneksi Database
- **Laragon:** Pastikan MySQL running (hijau di Laragon)
- **Laragon:** Default MySQL user `root` tanpa password
- Cek `config/database.php`
- Test koneksi: Buka HeidiSQL di Laragon

### Error Spatial Function
- Pastikan MySQL 5.7+ dengan spatial extension
- Test: `SELECT ST_GeomFromGeoJSON('{"type":"Point","coordinates":[0,0]}');`

### Data Tidak Muncul
- Pastikan import script sudah dijalankan
- Cek data di HeidiSQL: `SELECT COUNT(*) FROM sekolah;`
- Cek console browser (F12)
- Cek Network tab untuk error API
- **Refresh database** jika data sudah diubah (lihat bagian Refresh Database di atas)

### Laragon Specific
- **Apache/MySQL tidak start:** Restart Laragon sebagai Administrator
- **Port conflict:** Cek port 80 (Apache) dan 3306 (MySQL) tidak digunakan aplikasi lain
- **Virtual host tidak jalan:** Restart Laragon setelah setup virtual host

## 📝 Catatan

- Geocoding menggunakan Nominatim (OpenStreetMap) - gratis, rate limit 1 req/detik
- Koordinat menggunakan format WGS84 (SRID 4326)
- File GeoJSON harus ada di folder `data/` sebelum import

## 📄 Lisensi

Project untuk keperluan akademik (UAP Sistem Informasi Geografis).
