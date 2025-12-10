<?php
/**
 * Script Import GeoJSON ke MySQL
 * WebGIS Pendidikan Lampung Selatan
 * 
 * Usage: php database/import_geojson.php
 */

require_once __DIR__ . '/../config/database.php';

// Fungsi untuk menghapus dimensi ketiga (elevation) dari koordinat
function removeElevation($geometry) {
    if (!isset($geometry['coordinates'])) {
        return $geometry;
    }
    
    $type = $geometry['type'];
    $coordinates = $geometry['coordinates'];
    
    // Fungsi recursive untuk menghapus dimensi ketiga
    $removeThirdDim = function($coords) use (&$removeThirdDim) {
        if (is_array($coords)) {
            if (is_numeric($coords[0]) && is_numeric($coords[1])) {
                // Ini adalah koordinat [lon, lat, elevation?]
                return [$coords[0], $coords[1]]; // Hanya ambil lon dan lat
            } else {
                // Ini adalah array koordinat, rekursif
                return array_map($removeThirdDim, $coords);
            }
        }
        return $coords;
    };
    
    $geometry['coordinates'] = $removeThirdDim($coordinates);
    return $geometry;
}

// Fungsi untuk import Kecamatan (Administrasi)
function importKecamatan($conn, $filePath) {
    echo "Importing Kecamatan from: $filePath\n";
    
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['features'])) {
        die("Error: Invalid GeoJSON file\n");
    }
    
    // Clear existing data
    mysqli_query($conn, "TRUNCATE TABLE kecamatan");
    
    $count = 0;
    foreach ($data['features'] as $feature) {
        $properties = $feature['properties'];
        
        // Hapus dimensi ketiga (elevation) dari geometry
        $geometry_cleaned = removeElevation($feature['geometry']);
        $geometry = json_encode($geometry_cleaned);
        
        $nama_kecamatan = mysqli_real_escape_string($conn, $properties['NAMOBJ'] ?? '');
        $luas_km = isset($properties['Luas_KM']) ? floatval($properties['Luas_KM']) : 0.00;
        
        if (empty($nama_kecamatan)) continue;
        
        $geometry_escaped = mysqli_real_escape_string($conn, $geometry);
        // SRID 4326 sudah didefinisikan di schema, MySQL akan otomatis menggunakan SRID dari kolom
        $query = "INSERT INTO kecamatan (nama_kecamatan, luas_km, geometry) VALUES ('$nama_kecamatan', $luas_km, ST_GeomFromGeoJSON('$geometry_escaped'))";
        
        if (mysqli_query($conn, $query)) {
            $count++;
        } else {
            echo "Error inserting: " . mysqli_error($conn) . "\n";
        }
    }
    echo "Imported $count kecamatan records\n\n";
    return $count;
}

// Fungsi untuk import Kecamatan Analisis
function importKecamatanAnalisis($conn, $filePath) {
    echo "Importing Kecamatan Analisis from: $filePath\n";
    
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['features'])) {
        die("Error: Invalid GeoJSON file\n");
    }
    
    // Clear existing data
    mysqli_query($conn, "TRUNCATE TABLE kecamatan_analisis");
    
    $count = 0;
    foreach ($data['features'] as $feature) {
        $properties = $feature['properties'];
        
        // Hapus dimensi ketiga (elevation) dari geometry
        $geometry_cleaned = removeElevation($feature['geometry']);
        $geometry = json_encode($geometry_cleaned);
        
        $nama_kecamatan = mysqli_real_escape_string($conn, $properties['NAMOBJ'] ?? '');
        $jumlah_sekolah = isset($properties['NUMPOINTS']) ? intval($properties['NUMPOINTS']) : 0;
        $luas_km = isset($properties['Luas_KM']) ? floatval($properties['Luas_KM']) : 0.00;
        
        if (empty($nama_kecamatan)) continue;
        
        $geometry_escaped = mysqli_real_escape_string($conn, $geometry);
        // SRID 4326 sudah didefinisikan di schema, MySQL akan otomatis menggunakan SRID dari kolom
        $query = "INSERT INTO kecamatan_analisis (nama_kecamatan, jumlah_sekolah, luas_km, geometry) VALUES ('$nama_kecamatan', $jumlah_sekolah, $luas_km, ST_GeomFromGeoJSON('$geometry_escaped'))";
        
        if (mysqli_query($conn, $query)) {
            $count++;
        } else {
            echo "Error inserting: " . mysqli_error($conn) . "\n";
        }
    }
    echo "Imported $count kecamatan analisis records\n\n";
    return $count;
}

// Fungsi untuk import Sekolah (Digitasi)
function importSekolah($conn, $filePath) {
    echo "Importing Sekolah from: $filePath\n";
    
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);
    
    if (!$data || !isset($data['features'])) {
        die("Error: Invalid GeoJSON file\n");
    }
    
    // Clear existing data
    mysqli_query($conn, "TRUNCATE TABLE sekolah");
    
    $count = 0;
    foreach ($data['features'] as $feature) {
        $properties = $feature['properties'];
        
        // Hapus dimensi ketiga (elevation) dari geometry
        $geometry_cleaned = removeElevation($feature['geometry']);
        $geometry = $geometry_cleaned;
        
        $nama_sekolah = mysqli_real_escape_string($conn, $properties['NAMOBJ'] ?? '');
        $remark = $properties['REMARK'] ?? '';
        $fggpdk = isset($properties['FGGPDK']) ? intval($properties['FGGPDK']) : 0;
        
        // Extract jenjang from REMARK
        $jenjang = 'Lainnya';
        if (strpos($remark, 'Menengah Pertama') !== false) {
            $jenjang = 'Menengah Pertama';
        } elseif (strpos($remark, 'Menengah Umum') !== false) {
            $jenjang = 'Menengah Umum';
        } elseif (strpos($remark, 'Keagamaan') !== false) {
            $jenjang = 'Keagamaan';
        } elseif (strpos($remark, 'Tinggi') !== false) {
            $jenjang = 'Tinggi';
        } elseif (strpos($remark, 'Khusus') !== false) {
            $jenjang = 'Khusus';
        }
        
        $jenjang = mysqli_real_escape_string($conn, $jenjang);
        
        // Extract kecamatan from nama (jika ada) atau set null
        $kecamatan = 'NULL';
        
        // Get coordinates (sudah dibersihkan dari elevation)
        $coordinates = $geometry['coordinates'] ?? [];
        if (count($coordinates) < 2) continue;
        
        $longitude = floatval($coordinates[0]);
        $latitude = floatval($coordinates[1]);
        
        if (empty($nama_sekolah)) continue;
        
        $geometry_json = json_encode($geometry);
        $geometry_escaped = mysqli_real_escape_string($conn, $geometry_json);
        // SRID 4326 sudah didefinisikan di schema, MySQL akan otomatis menggunakan SRID dari kolom
        $query = "INSERT INTO sekolah (nama_sekolah, jenjang, fggpdk, kecamatan, latitude, longitude, geometry) VALUES ('$nama_sekolah', '$jenjang', $fggpdk, $kecamatan, $latitude, $longitude, ST_GeomFromGeoJSON('$geometry_escaped'))";
        
        if (mysqli_query($conn, $query)) {
            $count++;
        } else {
            echo "Error inserting: " . mysqli_error($conn) . "\n";
        }
    }
    echo "Imported $count sekolah records\n\n";
    return $count;
}

// Main execution
echo "========================================\n";
echo "Import GeoJSON to MySQL\n";
echo "WebGIS Pendidikan Lampung Selatan\n";
echo "========================================\n\n";

$basePath = __DIR__ . '/../data/';

// Import Kecamatan
$kecamatanFile = $basePath . 'administrasikecamatan.geojson';
if (file_exists($kecamatanFile)) {
    importKecamatan($conn, $kecamatanFile);
} else {
    echo "File not found: $kecamatanFile\n";
}

// Import Kecamatan Analisis
$analisisFile = $basePath . 'jumlahsekolahkecamatan.geojson';
if (file_exists($analisisFile)) {
    importKecamatanAnalisis($conn, $analisisFile);
} else {
    echo "File not found: $analisisFile\n";
}

// Import Sekolah
$sekolahFile = $basePath . 'digitasipendidikan.geojson';
if (file_exists($sekolahFile)) {
    importSekolah($conn, $sekolahFile);
} else {
    echo "File not found: $sekolahFile\n";
}

echo "========================================\n";
echo "Import completed!\n";
echo "========================================\n";

mysqli_close($conn);
?>

