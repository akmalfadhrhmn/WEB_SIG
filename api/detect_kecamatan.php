<?php
/**
 * API Endpoint: Detect Kecamatan dari Koordinat
 * Method: GET
 * 
 * Query Parameters:
 * - latitude: Latitude koordinat
 * - longitude: Longitude koordinat
 * 
 * Menggunakan spatial query untuk mencari kecamatan yang mengandung titik koordinat
 */

// Disable error display
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'error' => true,
        'message' => 'Method not allowed. Use GET.'
    ]);
    exit;
}

// Start output buffering
ob_start();

require_once __DIR__ . '/../config/database.php';

// Check connection
if (!$conn) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Get parameters
$latitude = isset($_GET['latitude']) ? floatval($_GET['latitude']) : 0;
$longitude = isset($_GET['longitude']) ? floatval($_GET['longitude']) : 0;

if ($latitude == 0 || $longitude == 0) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Latitude and longitude are required'
    ]);
    exit;
}

// Validate coordinates
if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid coordinates'
    ]);
    exit;
}

// Create point from coordinates
$point = "POINT($longitude $latitude)";

// Query untuk mencari kecamatan yang mengandung titik ini
$query = "SELECT id, nama_kecamatan, luas_km 
          FROM kecamatan 
          WHERE ST_Contains(geometry, ST_GeomFromText('$point', 4326))
          LIMIT 1";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Query error: ' . mysqli_error($conn)
    ]);
    exit;
}

// Clear output buffer
ob_end_clean();

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        'success' => true,
        'kecamatan' => $row['nama_kecamatan'],
        'id' => intval($row['id']),
        'luas_km' => floatval($row['luas_km'])
    ]);
} else {
    // Jika tidak ditemukan dalam batas kecamatan, return null
    echo json_encode([
        'success' => true,
        'kecamatan' => null,
        'message' => 'Koordinat tidak berada dalam batas kecamatan manapun'
    ]);
}

mysqli_close($conn);
exit;
?>

