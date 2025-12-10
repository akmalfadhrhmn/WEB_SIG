# Analisis Fitur WebGIS - Proyek SIG

## Status Implementasi Fitur Opsional

### ❌ 1. CRUD Data Titik (BELUM DIIMPLEMENTASIKAN)

**Status:** Tidak ada implementasi CRUD (Create, Read, Update, Delete) untuk data titik sekolah.

**Yang Sudah Ada:**
- ✅ **READ**: Endpoint `GET /api/get_sekolah.php` untuk membaca data sekolah
- ✅ Tampilan marker sekolah di peta dengan popup informasi

**Yang Belum Ada:**
- ❌ **CREATE**: Tidak ada endpoint untuk menambah data sekolah baru
- ❌ **UPDATE**: Tidak ada endpoint untuk mengubah data sekolah yang sudah ada
- ❌ **DELETE**: Tidak ada endpoint untuk menghapus data sekolah
- ❌ Form input untuk CRUD operations
- ❌ UI untuk edit/delete marker

**Yang Perlu Ditambahkan:**
1. Endpoint API:
   - `POST /api/create_sekolah.php` - Menambah sekolah baru
   - `PUT /api/update_sekolah.php` - Update data sekolah
   - `DELETE /api/delete_sekolah.php` - Hapus sekolah
2. Frontend:
   - Form modal untuk input data sekolah baru
   - Tombol edit/delete pada popup marker
   - Validasi input (nama, jenjang, koordinat)

---

### ⚠️ 2. Pencarian Lokasi (SEBAGIAN DIIMPLEMENTASIKAN)

**Status:** Ada fitur pencarian, tapi terbatas pada pencarian nama sekolah.

**Yang Sudah Ada:**
- ✅ Input search untuk mencari sekolah berdasarkan nama (`searchInput`)
- ✅ Filter real-time saat mengetik (debounce 500ms)
- ✅ Query parameter `search` di endpoint `get_sekolah.php`

**Yang Belum Ada:**
- ❌ **Geocoding**: Pencarian lokasi berdasarkan nama tempat/alamat (misal: "Kalianda", "Bandar Lampung")
- ❌ **Reverse Geocoding**: Menampilkan alamat dari koordinat yang diklik
- ❌ **Pencarian berdasarkan koordinat**: Input lat/lng untuk mencari lokasi
- ❌ **Auto-complete** untuk pencarian lokasi
- ❌ Integrasi dengan service geocoding (Google Maps Geocoding API, Nominatim, dll)

**Yang Perlu Ditambahkan:**
1. Integrasi dengan geocoding service (Nominatim/OpenStreetMap gratis)
2. Endpoint `GET /api/geocode.php?q=alamat` untuk geocoding
3. UI untuk input alamat dan menampilkan hasil geocoding
4. Fitur reverse geocoding saat klik peta

---

### ❌ 3. Menambah Marker dari Interface (BELUM DIIMPLEMENTASIKAN)

**Status:** Tidak ada fitur untuk menambah marker baru dari interface.

**Yang Sudah Ada:**
- ✅ Tampilan marker sekolah yang sudah ada
- ✅ Popup informasi saat klik marker

**Yang Belum Ada:**
- ❌ **Klik peta untuk menambah marker**: Tidak ada event listener untuk klik peta
- ❌ **Form input**: Tidak ada modal/form untuk input data sekolah baru
- ❌ **Mode edit**: Tidak ada toggle untuk mode "tambah marker"
- ❌ **Drag marker**: Tidak ada fitur untuk memindahkan marker
- ❌ **Validasi koordinat**: Tidak ada validasi apakah koordinat valid

**Yang Perlu Ditambahkan:**
1. Event listener `map.on('click')` untuk menangkap klik peta
2. Modal/form untuk input data sekolah saat klik peta
3. Toggle button untuk mode "Tambah Marker"
4. Integrasi dengan endpoint CREATE (setelah dibuat)
5. Visual feedback saat mode tambah marker aktif

---

## Dokumentasi Endpoint API

### Base URL
```
http://localhost/PROJEK_SIG/api/
```
atau
```
http://localhost:8000/api/  (jika menggunakan PHP built-in server)
```

---

### 1. GET `/api/get_kecamatan.php`

**Deskripsi:** Mengambil data batas administrasi kecamatan dalam format GeoJSON.

**Method:** `GET`

**Query Parameters:** Tidak ada

**Response Format:** GeoJSON FeatureCollection

**Response Example:**
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
      "geometry": {
        "type": "MultiPolygon",
        "coordinates": [[[[105.5, -5.9], [105.6, -5.9], ...]]]
      }
    }
  ]
}
```

**Kegunaan:**
- Menampilkan batas wilayah kecamatan di peta
- Menampilkan popup informasi (nama, luas) saat klik polygon

**Status Code:**
- `200 OK`: Data berhasil diambil
- `500 Internal Server Error`: Error database/query

---

### 2. GET `/api/get_kecamatan_analisis.php`

**Deskripsi:** Mengambil data hasil analisis jumlah sekolah per kecamatan dalam format GeoJSON.

**Method:** `GET`

**Query Parameters:** Tidak ada

**Response Format:** GeoJSON FeatureCollection

**Response Example:**
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
      "geometry": {
        "type": "MultiPolygon",
        "coordinates": [[[[105.5, -5.9], [105.6, -5.9], ...]]]
      }
    }
  ]
}
```

**Kegunaan:**
- Menampilkan choropleth map berdasarkan jumlah sekolah
- Analisis sebaran fasilitas pendidikan per kecamatan
- Menampilkan statistik di popup

**Status Code:**
- `200 OK`: Data berhasil diambil
- `500 Internal Server Error`: Error database/query

---

### 3. GET `/api/get_sekolah.php`

**Deskripsi:** Mengambil data titik sekolah (digitasi) dengan optional filtering.

**Method:** `GET`

**Query Parameters:**
- `jenjang` (optional): Filter berdasarkan jenjang pendidikan
  - Nilai: `"Menengah Pertama"`, `"Menengah Umum"`, `"Keagamaan"`, `"Tinggi"`, `"Khusus"`
  - Contoh: `?jenjang=Menengah Pertama`
- `search` (optional): Pencarian berdasarkan nama sekolah (LIKE query)
  - Contoh: `?search=SMP`
  - Case-insensitive partial match

**Response Format:** GeoJSON FeatureCollection

**Response Example:**
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {
        "id": 1,
        "nama_sekolah": "SMP Negeri 1 Rajabasa",
        "jenjang": "Menengah Pertama",
        "fggpdk": 12345,
        "kecamatan": "RAJABASA",
        "latitude": -5.934833,
        "longitude": 105.509354
      },
      "geometry": {
        "type": "Point",
        "coordinates": [105.509354, -5.934833]
      }
    }
  ]
}
```

**Contoh Request:**
```
GET /api/get_sekolah.php
GET /api/get_sekolah.php?jenjang=Menengah Pertama
GET /api/get_sekolah.php?search=SMP
GET /api/get_sekolah.php?jenjang=Menengah Pertama&search=SMP
```

**Kegunaan:**
- Menampilkan marker sekolah di peta
- Filter berdasarkan jenjang pendidikan
- Pencarian sekolah berdasarkan nama
- Menampilkan popup informasi saat klik marker

**Status Code:**
- `200 OK`: Data berhasil diambil
- `500 Internal Server Error`: Error database/query

**Catatan:**
- Filter `jenjang` dan `search` dapat dikombinasikan
- Jika `jenjang` = `"All"` atau kosong, semua jenjang ditampilkan
- Pencarian menggunakan `LIKE '%search%'` (partial match)

---

### 4. GET `/api/get_statistik.php`

**Deskripsi:** Mengambil statistik data sekolah dan kecamatan.

**Method:** `GET`

**Query Parameters:** Tidak ada

**Response Format:** JSON Object

**Response Example:**
```json
{
  "total_sekolah": 396,
  "total_kecamatan": 17,
  "per_jenjang": [
    {
      "jenjang": "Menengah Pertama",
      "jumlah": 180
    },
    {
      "jenjang": "Menengah Umum",
      "jumlah": 120
    },
    {
      "jenjang": "Keagamaan",
      "jumlah": 50
    },
    {
      "jenjang": "Tinggi",
      "jumlah": 30
    },
    {
      "jenjang": "Khusus",
      "jumlah": 16
    }
  ],
  "per_kecamatan": [
    {
      "nama_kecamatan": "KALIANDA",
      "jumlah_sekolah": 45,
      "luas_km": 15.2
    },
    {
      "nama_kecamatan": "RAJABASA",
      "jumlah_sekolah": 38,
      "luas_km": 12.3
    }
  ]
}
```

**Kegunaan:**
- Menampilkan dashboard statistik di sidebar
- Total sekolah dan kecamatan
- Distribusi sekolah per jenjang
- Top kecamatan dengan jumlah sekolah terbanyak

**Status Code:**
- `200 OK`: Data berhasil diambil
- `500 Internal Server Error`: Error database/query

**Catatan:**
- `per_kecamatan` dibatasi 20 kecamatan teratas (ORDER BY jumlah_sekolah DESC)
- Data diurutkan berdasarkan jumlah sekolah (terbanyak ke terkecil)

---

## Endpoint yang Belum Ada (Untuk CRUD)

### ❌ POST `/api/create_sekolah.php`
**Status:** Belum diimplementasikan

**Deskripsi:** Menambah data sekolah baru ke database.

**Method:** `POST`

**Request Body (JSON):**
```json
{
  "nama_sekolah": "SMP Negeri 1 Baru",
  "jenjang": "Menengah Pertama",
  "fggpdk": 12345,
  "kecamatan": "RAJABASA",
  "latitude": -5.934833,
  "longitude": 105.509354
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sekolah berhasil ditambahkan",
  "id": 123
}
```

---

### ❌ PUT `/api/update_sekolah.php`
**Status:** Belum diimplementasikan

**Deskripsi:** Mengupdate data sekolah yang sudah ada.

**Method:** `PUT`

**Query Parameters:**
- `id`: ID sekolah yang akan diupdate

**Request Body (JSON):**
```json
{
  "nama_sekolah": "SMP Negeri 1 Updated",
  "jenjang": "Menengah Pertama",
  "fggpdk": 12345,
  "kecamatan": "RAJABASA",
  "latitude": -5.934833,
  "longitude": 105.509354
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sekolah berhasil diupdate"
}
```

---

### ❌ DELETE `/api/delete_sekolah.php`
**Status:** Belum diimplementasikan

**Deskripsi:** Menghapus data sekolah dari database.

**Method:** `DELETE`

**Query Parameters:**
- `id`: ID sekolah yang akan dihapus

**Response:**
```json
{
  "success": true,
  "message": "Sekolah berhasil dihapus"
}
```

---

## Kesimpulan

### Fitur Wajib (Sudah Diimplementasikan) ✅
1. ✅ Tampilan peta interaktif dengan Leaflet.js
2. ✅ Layer batas kecamatan (polygon)
3. ✅ Layer hasil analisis (choropleth)
4. ✅ Layer titik sekolah (marker)
5. ✅ Popup informasi
6. ✅ Filter berdasarkan jenjang
7. ✅ Pencarian nama sekolah
8. ✅ Toggle layer
9. ✅ Statistik dashboard

### Fitur Opsional (Belum Diimplementasikan) ❌
1. ❌ **CRUD data titik** - Perlu ditambahkan endpoint CREATE, UPDATE, DELETE
2. ⚠️ **Pencarian lokasi** - Hanya pencarian nama sekolah, belum ada geocoding
3. ❌ **Menambah marker dari interface** - Perlu ditambahkan event listener klik peta dan form input

### Rekomendasi
Untuk mendapatkan nilai plus, perlu menambahkan:
1. Endpoint CRUD untuk data sekolah
2. Fitur tambah marker dengan klik peta
3. Form input/edit untuk data sekolah
4. Geocoding untuk pencarian lokasi (opsional, bisa menggunakan Nominatim gratis)

