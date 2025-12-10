<?php
/**
 * API Endpoint: Create Sekolah (Titik Digitasi)
 * Method: POST
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

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

ob_start();

require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Database connection failed'
    ]);
    exit;
}

$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

if (!$input) {
    ob_end_clean();
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
    if (!isset($input[$field]) || (is_string($input[$field]) && trim($input[$field]) === '')) {
        ob_end_clean();
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
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid coordinates'
    ]);
    exit;
}

// Validate jenjang
$valid_jenjang = ['Menengah Pertama', 'Menengah Umum', 'Keagamaan', 'Tinggi', 'Khusus'];
if (!in_array($jenjang, $valid_jenjang)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid jenjang'
    ]);
    exit;
}

// Create GeoJSON Point
$geojson_point = json_encode([
    'type' => 'Point',
    'coordinates' => [$longitude, $latitude]
]);
$geojson_escaped = mysqli_real_escape_string($conn, $geojson_point);

// Insert using prepared statement
$query = "INSERT INTO sekolah (nama_sekolah, jenjang, fggpdk, kecamatan, latitude, longitude, geometry) 
          VALUES (?, ?, ?, ?, ?, ?, ST_GeomFromGeoJSON(?, 1, 4326))";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    ob_end_clean();
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
    $latitude,          // d
    $longitude,         // d
    $geojson_point      // s
);

$result = mysqli_stmt_execute($stmt);

ob_end_clean();

if ($result) {
    $new_id = mysqli_insert_id($conn);
    
    // Update jumlah sekolah di kecamatan_analisis jika kecamatan dipilih
    if (!empty($kecamatan)) {
        $kecamatan_escaped = mysqli_real_escape_string($conn, $kecamatan);
        $update_query = "UPDATE kecamatan_analisis SET jumlah_sekolah = jumlah_sekolah + 1 WHERE nama_kecamatan = '$kecamatan_escaped'";
        $update_result = mysqli_query($conn, $update_query);
        if (!$update_result) {
            error_log("Failed to update kecamatan_analisis: " . mysqli_error($conn));
        }
    }
    
    mysqli_stmt_close($stmt);
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Sekolah berhasil ditambahkan',
        'id' => $new_id,
        'kecamatan' => $kecamatan
    ]);
} else {
    $error_msg = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Failed to insert: ' . $error_msg
    ]);
}

mysqli_close($conn);
exit;
?>
