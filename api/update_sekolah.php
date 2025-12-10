<?php
/**
 * API Endpoint: Update Sekolah (Titik Digitasi)
 * Method: PUT
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type');

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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid or missing ID parameter'
    ]);
    exit;
}

// Get kecamatan lama SEBELUM update
$old_kecamatan = '';
$check_query = "SELECT kecamatan FROM sekolah WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
if ($check_stmt) {
    mysqli_stmt_bind_param($check_stmt, 'i', $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        $old_row = mysqli_fetch_assoc($check_result);
        $old_kecamatan = $old_row['kecamatan'] ? mysqli_real_escape_string($conn, $old_row['kecamatan']) : '';
    } else {
        mysqli_stmt_close($check_stmt);
        ob_end_clean();
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'message' => 'Sekolah tidak ditemukan'
        ]);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($check_stmt);
} else {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Failed to check sekolah: ' . mysqli_error($conn)
    ]);
    mysqli_close($conn);
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

// Update using prepared statement
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
    ob_end_clean();
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
    $kecamatan,          // s
    $latitude,           // d
    $longitude,          // d
    $geojson_point,      // s
    $id                  // i
);

$result = mysqli_stmt_execute($stmt);

ob_end_clean();

if ($result) {
    // Update jumlah sekolah di kecamatan_analisis
    // Kurangi dari kecamatan lama (jika ada dan berbeda)
    if (!empty($old_kecamatan) && $old_kecamatan != $kecamatan) {
        $old_kecamatan_escaped = mysqli_real_escape_string($conn, $old_kecamatan);
        $decrease_query = "UPDATE kecamatan_analisis SET jumlah_sekolah = GREATEST(jumlah_sekolah - 1, 0) WHERE nama_kecamatan = '$old_kecamatan_escaped'";
        $decrease_result = mysqli_query($conn, $decrease_query);
        if (!$decrease_result) {
            error_log("Failed to decrease kecamatan_analisis: " . mysqli_error($conn));
        }
    }
    
    // Tambah ke kecamatan baru (jika ada dan berbeda dari lama)
    if (!empty($kecamatan) && $kecamatan != $old_kecamatan) {
        $kecamatan_escaped = mysqli_real_escape_string($conn, $kecamatan);
        $increase_query = "UPDATE kecamatan_analisis SET jumlah_sekolah = jumlah_sekolah + 1 WHERE nama_kecamatan = '$kecamatan_escaped'";
        $increase_result = mysqli_query($conn, $increase_query);
        if (!$increase_result) {
            error_log("Failed to increase kecamatan_analisis: " . mysqli_error($conn));
        }
    }
    
    mysqli_stmt_close($stmt);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Sekolah berhasil diupdate',
        'id' => $id,
        'kecamatan' => $kecamatan
    ]);
} else {
    $error_msg = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Failed to update: ' . $error_msg
    ]);
}

mysqli_close($conn);
exit;
?>
