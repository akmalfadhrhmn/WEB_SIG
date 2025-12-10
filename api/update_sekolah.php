<?php
/**
 * API Endpoint: Update Sekolah (Titik Digitasi)
 * Method: PUT
 * 
 * Query Parameters:
 * - id: ID sekolah yang akan diupdate
 * 
 * Request Body (JSON):
 * {
 *   "nama_sekolah": "SMP Negeri 1 Updated",
 *   "jenjang": "Menengah Pertama",
 *   "fggpdk": 12345,
 *   "kecamatan": "RAJABASA",
 *   "latitude": -5.934833,
 *   "longitude": 105.509354
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'error' => true,
        'message' => 'Method not allowed. Use PUT.'
    ]);
    exit;
}

require_once __DIR__ . '/../config/database.php';

// Get ID from query parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid or missing ID parameter'
    ]);
    exit;
}

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

// Check if sekolah exists
$check_query = "SELECT id FROM sekolah WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, 'i', $id);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'message' => 'Sekolah tidak ditemukan'
    ]);
    mysqli_stmt_close($check_stmt);
    mysqli_close($conn);
    exit;
}
mysqli_stmt_close($check_stmt);

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

// Update database
$query = "UPDATE sekolah 
          SET nama_sekolah = ?, 
              jenjang = ?, 
              fggpdk = ?, 
              kecamatan = ?, 
              latitude = ?, 
              longitude = ?, 
              geometry = ST_GeomFromGeoJSON(?, 1, 4326)
          WHERE id = ?";

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
mysqli_stmt_bind_param($stmt, 'ssisdssi', 
    $nama_sekolah,      // s
    $jenjang,           // s
    $fggpdk,            // i
    $kecamatan,         // s
    $latitude,          // d (double)
    $longitude,         // d (double)
    $geojson_point,     // s
    $id                  // i
);

if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Sekolah berhasil diupdate',
        'id' => $id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Failed to update: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

