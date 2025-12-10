<?php
/**
 * API Endpoint: Get Sekolah (Titik Digitasi)
 * Returns GeoJSON format with optional filtering
 * 
 * Query Parameters:
 * - jenjang: Filter by jenjang (Menengah Pertama, Menengah Umum, Keagamaan, Tinggi, Khusus)
 * - search: Search by nama_sekolah
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/database.php';

$jenjang = isset($_GET['jenjang']) ? mysqli_real_escape_string($conn, $_GET['jenjang']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$query = "SELECT 
    id,
    nama_sekolah,
    jenjang,
    fggpdk,
    kecamatan,
    latitude,
    longitude,
    ST_AsGeoJSON(geometry) as geometry
FROM sekolah
WHERE 1=1";

if (!empty($jenjang) && $jenjang !== 'All') {
    $query .= " AND jenjang = '$jenjang'";
}

if (!empty($search)) {
    $query .= " AND nama_sekolah LIKE '%$search%'";
}

$query .= " ORDER BY nama_sekolah";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => "Query error: " . mysqli_error($conn)
    ]);
    exit;
}

$features = [];

while ($row = mysqli_fetch_assoc($result)) {
    $geometry = json_decode($row['geometry'], true);
    
    if ($geometry) {
        $features[] = [
            'type' => 'Feature',
            'properties' => [
                'id' => intval($row['id']),
                'nama_sekolah' => $row['nama_sekolah'],
                'jenjang' => $row['jenjang'],
                'fggpdk' => intval($row['fggpdk']),
                'kecamatan' => $row['kecamatan'],
                'latitude' => floatval($row['latitude']),
                'longitude' => floatval($row['longitude'])
            ],
            'geometry' => $geometry
        ];
    }
}

$geojson = [
    'type' => 'FeatureCollection',
    'features' => $features
];

echo json_encode($geojson, JSON_NUMERIC_CHECK);

mysqli_close($conn);
?>

