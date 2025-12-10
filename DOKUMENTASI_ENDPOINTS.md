# Dokumentasi Endpoints API - WebGIS Pendidikan

## 📋 Daftar Endpoints

### GET Endpoints (Membaca Data)

#### 1. `GET /api/get_kecamatan.php`
**Fungsi:** Mengambil data batas administrasi kecamatan dalam format GeoJSON.

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `loadKecamatan()`
- Menampilkan layer polygon batas kecamatan di peta

**Response Format:** GeoJSON FeatureCollection
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "id": 1,
        "nama_kecamatan": "RAJABASA",
        "luas_km": 12.3
      },
      "geometry": { ... }
    }
  ]
}
```

**Hubungan dengan file lain:**
- **Database:** Tabel `kecamatan`
- **Frontend:** `assets/js/map.js` → `kecamatanLayer`
- **Data source:** `data/administrasikecamatan.geojson` (diimport via `database/import_geojson.php`)

---

#### 2. `GET /api/get_kecamatan_analisis.php`
**Fungsi:** Mengambil data hasil analisis jumlah sekolah per kecamatan (choropleth map).

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `loadKecamatanAnalisis()`
- Menampilkan layer polygon dengan warna berdasarkan jumlah sekolah

**Response Format:** GeoJSON FeatureCollection
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "id": 1,
        "nama_kecamatan": "RAJABASA",
        "jumlah_sekolah": 25,
        "luas_km": 12.3
      },
      "geometry": { ... }
    }
  ]
}
```

**Hubungan dengan file lain:**
- **Database:** Tabel `kecamatan_analisis`
- **Frontend:** `assets/js/map.js` → `analisisLayer`
- **Data source:** `data/jumlahsekolahkecamatan.geojson` (diimport via `database/import_geojson.php`)

---

#### 3. `GET /api/get_sekolah.php`
**Fungsi:** Mengambil data titik sekolah dengan optional filtering.

**Query Parameters:**
- `jenjang` (optional): Filter berdasarkan jenjang pendidikan
- `search` (optional): Pencarian berdasarkan nama sekolah

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `loadSekolah()`
- Menampilkan marker sekolah di peta
- Filter dan pencarian real-time

**Response Format:** GeoJSON FeatureCollection
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "id": 1,
        "nama_sekolah": "SMP Negeri 1",
        "jenjang": "Menengah Pertama",
        "kecamatan": "RAJABASA",
        "latitude": -5.934833,
        "longitude": 105.509354
      },
      "geometry": { ... }
    }
  ]
}
```

**Hubungan dengan file lain:**
- **Database:** Tabel `sekolah`
- **Frontend:** `assets/js/map.js` → `sekolahLayer`, filter di sidebar (`index.php`)
- **Data source:** `data/digitasipendidikan.geojson` (diimport via `database/import_geojson.php`)

---

#### 4. `GET /api/get_statistik.php`
**Fungsi:** Mengambil statistik data sekolah dan kecamatan.

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `loadStatistik()`
- Menampilkan dashboard statistik di sidebar

**Response Format:** JSON Object
```json
{
  "total_sekolah": 396,
  "total_kecamatan": 17,
  "per_jenjang": [
    { "jenjang": "Menengah Pertama", "jumlah": 180 }
  ],
  "per_kecamatan": [
    { "nama_kecamatan": "KALIANDA", "jumlah_sekolah": 45 }
  ]
}
```

**Hubungan dengan file lain:**
- **Database:** Tabel `sekolah`, `kecamatan`, `kecamatan_analisis`
- **Frontend:** `assets/js/map.js` → `statistikContent` di sidebar (`index.php`)

---

#### 5. `GET /api/geocode.php?q={query}&limit={limit}`
**Fungsi:** Geocoding - mencari lokasi berdasarkan nama tempat/alamat.

**Query Parameters:**
- `q`: Query string (nama tempat/alamat) - **wajib**
- `limit`: Jumlah hasil maksimal (default: 5) - **opsional**

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `geocodeLocation()`
- Input "Cari Lokasi" di sidebar (`index.php`)

**Response Format:** JSON Object
```json
{
  "success": true,
  "query": "Kalianda",
  "count": 3,
  "results": [
    {
      "display_name": "Kalianda, Lampung Selatan...",
      "latitude": -5.7375,
      "longitude": 105.5917
    }
  ]
}
```

**Hubungan dengan file lain:**
- **External API:** Nominatim (OpenStreetMap) - tidak ada database lokal
- **Frontend:** `assets/js/map.js` → `geocodeInput`, `geocodeResults` di sidebar (`index.php`)

---

#### 6. `GET /api/detect_kecamatan.php?latitude={lat}&longitude={lng}`
**Fungsi:** Auto-detect kecamatan dari koordinat menggunakan spatial query.

**Query Parameters:**
- `latitude`: Latitude koordinat - **wajib**
- `longitude`: Longitude koordinat - **wajib**

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `detectKecamatan()`, `openSekolahModal()`
- Auto-detect kecamatan saat create/edit sekolah
- Auto-fill kecamatan di popup jika kosong

**Response Format:** JSON Object
```json
{
  "success": true,
  "kecamatan": "NATAR",
  "id": 5,
  "luas_km": 15.2
}
```

**Hubungan dengan file lain:**
- **Database:** Tabel `kecamatan` (spatial query dengan `ST_Contains`)
- **Frontend:** `assets/js/map.js` → form modal, popup marker
- **Backend:** `api/create_sekolah.php`, `api/update_sekolah.php` (juga menggunakan logic yang sama)

---

### POST Endpoints (Create Data)

#### 7. `POST /api/create_sekolah.php`
**Fungsi:** Menambah sekolah baru ke database.

**Request Body (JSON):**
```json
{
  "nama_sekolah": "SMP Negeri 1 Baru",
  "jenjang": "Menengah Pertama",
  "fggpdk": 12345,
  "latitude": -5.934833,
  "longitude": 105.509354
}
```

**Catatan:** 
- Field `kecamatan` tidak perlu dikirim, akan auto-detect dari koordinat
- Field `fggpdk` (Kode Identifikasi Sekolah) bersifat opsional, default: 0

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `createSekolah()`
- Form modal saat submit (create mode)

**Response Format:** JSON Object
```json
{
  "success": true,
  "message": "Sekolah berhasil ditambahkan",
  "id": 123,
  "kecamatan": "NATAR"
}
```

**Hubungan dengan file lain:**
- **Database:** Tabel `sekolah` (INSERT query)
- **Frontend:** `assets/js/map.js` → form submit handler
- **Auto-detect:** Menggunakan spatial query ke tabel `kecamatan` untuk detect kecamatan
- **Update:** Setelah create, memanggil `loadSekolah()` dan `loadStatistik()` untuk refresh data

---

### PUT Endpoints (Update Data)

#### 8. `PUT /api/update_sekolah.php?id={id}`
**Fungsi:** Mengupdate data sekolah yang sudah ada.

**Query Parameters:**
- `id`: ID sekolah yang akan diupdate - **wajib**

**Request Body (JSON):**
```json
{
  "nama_sekolah": "SMP Negeri 1 Updated",
  "jenjang": "Menengah Pertama",
  "fggpdk": 12345,
  "latitude": -5.934833,
  "longitude": 105.509354
}
```

**Catatan:** Field `kecamatan` tidak perlu dikirim, akan auto-detect dari koordinat baru.

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `updateSekolah()`
- Form modal saat submit (edit mode)
- Tombol "Edit" di popup marker

**Response Format:** JSON Object
```json
{
  "success": true,
  "message": "Sekolah berhasil diupdate",
  "id": 123,
  "kecamatan": "NATAR"
}
```

**Hubungan dengan file lain:**
- **Database:** Tabel `sekolah` (UPDATE query)
- **Frontend:** `assets/js/map.js` → form submit handler, `editSekolah()` function
- **Auto-detect:** Menggunakan spatial query ke tabel `kecamatan` untuk detect kecamatan baru
- **Update:** Setelah update, memanggil `loadSekolah()` dan `loadStatistik()` untuk refresh data

---

### DELETE Endpoints (Delete Data)

#### 9. `DELETE /api/delete_sekolah.php?id={id}`
**Fungsi:** Menghapus sekolah dari database.

**Query Parameters:**
- `id`: ID sekolah yang akan dihapus - **wajib**

**Digunakan oleh:**
- `assets/js/map.js` → fungsi `deleteSekolah()`
- Tombol "Hapus" di popup marker

**Response Format:** JSON Object
```json
{
  "success": true,
  "message": "Sekolah berhasil dihapus",
  "deleted_id": 123,
  "deleted_nama": "SMP Negeri 1"
}
```

**Hubungan dengan file lain:**
- **Database:** Tabel `sekolah` (DELETE query)
- **Frontend:** `assets/js/map.js` → `deleteSekolah()` function, popup marker
- **Update:** Setelah delete, menghapus marker dari map dan memanggil `loadSekolah()`, `loadStatistik()` untuk refresh

---

## 🔄 Alur Data dan Hubungan File

### 1. Alur Load Data Awal
```
index.php (halaman utama)
  ↓
assets/js/map.js (DOMContentLoaded)
  ↓
loadKecamatan() → GET /api/get_kecamatan.php → database/kecamatan
loadKecamatanAnalisis() → GET /api/get_kecamatan_analisis.php → database/kecamatan_analisis
loadSekolah() → GET /api/get_sekolah.php → database/sekolah
loadStatistik() → GET /api/get_statistik.php → database/sekolah, kecamatan, kecamatan_analisis
```

### 2. Alur Create Sekolah
```
User klik "Tambah Marker" (index.php sidebar)
  ↓
toggleAddMode() (map.js) → Cek layer visibility
  ↓
User klik peta → openSekolahModal() → detectKecamatan() → GET /api/detect_kecamatan.php
  ↓
User isi form → Submit → createSekolah() → POST /api/create_sekolah.php
  ↓
Backend auto-detect kecamatan → INSERT ke database/sekolah
  ↓
Response → loadSekolah() + loadStatistik() → Refresh peta
```

### 3. Alur Update Sekolah
```
User klik marker → Popup muncul → Klik "Edit"
  ↓
openSekolahModal(id) → detectKecamatan() → GET /api/detect_kecamatan.php
  ↓
User ubah data → Submit → updateSekolah() → PUT /api/update_sekolah.php?id={id}
  ↓
Backend auto-detect kecamatan baru → UPDATE database/sekolah
  ↓
Response → loadSekolah() + loadStatistik() → Refresh peta
```

### 4. Alur Delete Sekolah
```
User klik marker → Popup muncul → Klik "Hapus"
  ↓
Konfirmasi → deleteSekolah() → DELETE /api/delete_sekolah.php?id={id}
  ↓
DELETE dari database/sekolah
  ↓
Response → Hapus marker dari map + loadSekolah() + loadStatistik()
```

### 5. Alur Geocoding
```
User ketik di "Cari Lokasi" (index.php sidebar)
  ↓
Klik "Cari" → geocodeLocation() → GET /api/geocode.php?q={query}
  ↓
Backend call Nominatim API (external)
  ↓
Response → Tampilkan hasil di sidebar
  ↓
User klik hasil → selectGeocodeResult() → Pindah peta ke lokasi
```

---

## 🗄️ Hubungan dengan Database

### Tabel `kecamatan`
- **Digunakan oleh:**
  - `get_kecamatan.php` - Read batas kecamatan
  - `detect_kecamatan.php` - Spatial query untuk detect kecamatan
  - `create_sekolah.php` - Auto-detect kecamatan saat create
  - `update_sekolah.php` - Auto-detect kecamatan saat update

### Tabel `kecamatan_analisis`
- **Digunakan oleh:**
  - `get_kecamatan_analisis.php` - Read hasil analisis
  - `get_statistik.php` - Statistik per kecamatan

### Tabel `sekolah`
- **Digunakan oleh:**
  - `get_sekolah.php` - Read data sekolah
  - `get_statistik.php` - Statistik sekolah
  - `create_sekolah.php` - INSERT sekolah baru
  - `update_sekolah.php` - UPDATE sekolah
  - `delete_sekolah.php` - DELETE sekolah

---

## 📁 Struktur File dan Dependencies

```
PROJEK_SIG/
├── api/                          # Backend API Endpoints
│   ├── get_kecamatan.php         # → database/kecamatan
│   ├── get_kecamatan_analisis.php # → database/kecamatan_analisis
│   ├── get_sekolah.php          # → database/sekolah
│   ├── get_statistik.php        # → database/sekolah, kecamatan, kecamatan_analisis
│   ├── geocode.php              # → External API (Nominatim)
│   ├── detect_kecamatan.php     # → database/kecamatan (spatial query)
│   ├── create_sekolah.php       # → database/sekolah + kecamatan (auto-detect)
│   ├── update_sekolah.php       # → database/sekolah + kecamatan (auto-detect)
│   └── delete_sekolah.php       # → database/sekolah
│
├── assets/js/map.js             # Frontend JavaScript
│   ├── loadKecamatan()          # → get_kecamatan.php
│   ├── loadKecamatanAnalisis()  # → get_kecamatan_analisis.php
│   ├── loadSekolah()            # → get_sekolah.php
│   ├── loadStatistik()          # → get_statistik.php
│   ├── geocodeLocation()        # → geocode.php
│   ├── detectKecamatan()        # → detect_kecamatan.php
│   ├── createSekolah()          # → create_sekolah.php
│   ├── updateSekolah()          # → update_sekolah.php
│   └── deleteSekolah()          # → delete_sekolah.php
│
├── config/database.php          # Database connection (digunakan semua API)
├── database/
│   ├── schema.sql               # Database schema
│   └── import_geojson.php      # Import data GeoJSON ke database
└── index.php                    # Halaman utama (UI)
```

---

## 🔐 Keamanan

**Catatan Penting:**
- Semua endpoint CRUD menggunakan `mysqli_real_escape_string()` untuk sanitasi input
- Query menggunakan query biasa (bukan prepared statement) sesuai permintaan
- Validasi input dilakukan di backend
- CORS headers di-set untuk allow cross-origin requests

**Rekomendasi:**
- Untuk production, pertimbangkan menggunakan prepared statement untuk keamanan lebih baik
- Tambahkan authentication/authorization jika diperlukan

---

## 📝 Catatan Teknis

1. **Auto-detect Kecamatan:**
   - Menggunakan spatial query `ST_Contains()` untuk mencari kecamatan yang mengandung titik koordinat
   - Digunakan di `create_sekolah.php`, `update_sekolah.php`, dan `detect_kecamatan.php`

2. **Layer Visibility Check:**
   - Tambah marker hanya bisa dilakukan jika layer "Batas Kecamatan" dan "Hasil Analisis" sudah di-off
   - Implementasi di `map.js` → `toggleAddMode()`

3. **Real-time Update:**
   - Setelah CRUD operation, data di-refresh dengan memanggil `loadSekolah()` dan `loadStatistik()`
   - Marker dihapus langsung dari map saat delete (tidak perlu reload semua)

4. **Geocoding:**
   - Menggunakan Nominatim (OpenStreetMap) - gratis, rate limit 1 req/detik
   - User-Agent header wajib (sudah diimplementasikan)

