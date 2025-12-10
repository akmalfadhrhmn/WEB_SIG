<?php
/**
 * API Endpoint: Create Sekolah (Titik Digitasi)
 * Method: POST
 * 
 * Request Body (JSON):
 * {
 *   "nama_sekolah": "SMP Negeri 1",
 *   "jenjang": "Menengah Pertama",
 *   "fggpdk": 12345,
 *   "kecamatan": "RAJABASA",
 *   "latitude": -5.934833,
 *   "longitude": 105.509354
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => true,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid JSON input'
    ]);
    exit;
}

// Validate required fields
$required = ['nama_sekolah', 'jenjang', 'latitude', 'longitude'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => "Field '$field' is required"
        ]);
        exit;
    }
}

// Sanitize input
$nama_sekolah = mysqli_real_escape_string($conn, trim($input['nama_sekolah']));
$jenjang = mysqli_real_escape_string($conn, trim($input['jenjang']));
$fggpdk = isset($input['fggpdk']) ? intval($input['fggpdk']) : 0;
$kecamatan = isset($input['kecamatan']) ? mysqli_real_escape_string($conn, trim($input['kecamatan'])) : '';
$latitude = floatval($input['latitude']);
$longitude = floatval($input['longitude']);

// Validate coordinates
if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid coordinates. Latitude must be between -90 and 90, Longitude between -180 and 180.'
    ]);
    exit;
}

// Validate jenjang
$valid_jenjang = ['Menengah Pertama', 'Menengah Umum', 'Keagamaan', 'Tinggi', 'Khusus'];
if (!in_array($jenjang, $valid_jenjang)) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid jenjang. Must be one of: ' . implode(', ', $valid_jenjang)
    ]);
    exit;
}

// Create GeoJSON Point
$geojson_point = json_encode([
    'type' => 'Point',
    'coordinates' => [$longitude, $latitude]
]);

// Insert into database
$query = "INSERT INTO sekolah (nama_sekolah, jenjang, fggpdk, kecamatan, latitude, longitude, geometry) 
          VALUES (?, ?, ?, ?, ?, ?, ST_GeomFromGeoJSON(?, 1, 4326))";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Prepare statement failed: ' . mysqli_error($conn)
    ]);
    exit;
}

// Bind parameters: s=string, i=integer, d=double
mysqli_stmt_bind_param($stmt, 'ssisdds', 
    $nama_sekolah,      // s
    $jenjang,           // s
    $fggpdk,            // i
    $kecamatan,         // s
    $latitude,           // d (double)
    $longitude,         // d (double)
    $geojson_point      // s
);

if (mysqli_stmt_execute($stmt)) {
    $new_id = mysqli_insert_id($conn);
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Sekolah berhasil ditambahkan',
        'id' => $new_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Failed to insert: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

