# Penjelasan FGGPDK (Kode Identifikasi Sekolah)

## Apa itu FGGPDK?

**FGGPDK** adalah singkatan dari **"Fasilitas Geografis Pendidikan"** atau **"Kode Identifikasi Sekolah"** yang digunakan dalam sistem administrasi pendidikan untuk mengidentifikasi fasilitas pendidikan secara unik.

## Penjelasan untuk Orang Awam

FGGPDK adalah **kode nomor identifikasi** yang diberikan kepada setiap sekolah atau fasilitas pendidikan. Kode ini mirip seperti:
- **NISN (Nomor Induk Siswa Nasional)** untuk siswa
- **NPSN (Nomor Pokok Sekolah Nasional)** untuk sekolah
- **Kode pos** untuk alamat

### Fungsi FGGPDK:
1. **Identifikasi Unik**: Setiap sekolah memiliki kode yang berbeda untuk membedakan satu sama lain
2. **Administrasi**: Memudahkan pengelolaan data sekolah dalam sistem database
3. **Referensi Data**: Digunakan untuk menghubungkan data sekolah dengan sistem lain

## Di Aplikasi WebGIS Ini

- **Field FGGPDK bersifat Opsional**: Tidak wajib diisi saat menambah atau mengedit sekolah
- **Default Value**: Jika tidak diisi, akan disimpan sebagai `0`
- **Tipe Data**: Angka (integer)
- **Contoh**: `12345`, `67890`, dll

## Kapan Menggunakan FGGPDK?

- Jika sekolah memiliki kode identifikasi resmi dari dinas pendidikan
- Jika ingin menghubungkan data dengan sistem administrasi lain
- Jika diperlukan untuk keperluan pelaporan atau administrasi

## Catatan

Jika Anda tidak tahu kode FGGPDK sekolah, **biarkan kosong** atau isi dengan `0`. Aplikasi tetap berfungsi normal tanpa kode ini.

---

**Kesimpulan:** FGGPDK adalah kode identifikasi sekolah yang opsional. Jika tidak ada, tidak perlu diisi.

