<?php
/**
 * API Endpoint: Get Kecamatan (Batas Administrasi)
 * Returns GeoJSON format
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/database.php';

$query = "SELECT 
    id,
    nama_kecamatan,
    luas_km,
    ST_AsGeoJSON(geometry) as geometry
FROM kecamatan
ORDER BY nama_kecamatan";

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
                'nama_kecamatan' => $row['nama_kecamatan'],
                'luas_km' => floatval($row['luas_km'])
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

