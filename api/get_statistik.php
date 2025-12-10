<?php
/**
 * API Endpoint: Get Statistik
 * Returns statistics about sekolah data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/database.php';

$statistik = [];

// Total Sekolah
$query = "SELECT COUNT(*) as total FROM sekolah";
$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => "Query error: " . mysqli_error($conn)
    ]);
    exit;
}

$row = mysqli_fetch_assoc($result);
$statistik['total_sekolah'] = intval($row['total']);

// Jumlah per Jenjang
$query = "SELECT jenjang, COUNT(*) as jumlah 
          FROM sekolah 
          GROUP BY jenjang 
          ORDER BY jumlah DESC";
$result = mysqli_query($conn, $query);

$per_jenjang = [];
while ($row = mysqli_fetch_assoc($result)) {
    $per_jenjang[] = [
        'jenjang' => $row['jenjang'],
        'jumlah' => intval($row['jumlah'])
    ];
}
$statistik['per_jenjang'] = $per_jenjang;

// Sebaran per Kecamatan (dari kecamatan_analisis)
$query = "SELECT nama_kecamatan, jumlah_sekolah, luas_km
          FROM kecamatan_analisis
          ORDER BY jumlah_sekolah DESC
          LIMIT 20";
$result = mysqli_query($conn, $query);

$per_kecamatan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $per_kecamatan[] = [
        'nama_kecamatan' => $row['nama_kecamatan'],
        'jumlah_sekolah' => intval($row['jumlah_sekolah']),
        'luas_km' => floatval($row['luas_km'])
    ];
}
$statistik['per_kecamatan'] = $per_kecamatan;

// Total Kecamatan
$query = "SELECT COUNT(*) as total FROM kecamatan";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$statistik['total_kecamatan'] = intval($row['total']);

echo json_encode($statistik, JSON_NUMERIC_CHECK);

mysqli_close($conn);
?>

