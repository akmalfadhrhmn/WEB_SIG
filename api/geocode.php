<?php
/**
 * API Endpoint: Geocoding (Pencarian Lokasi)
 * Method: GET
 * 
 * Query Parameters:
 * - q: Query string (nama tempat/alamat)
 * - limit: Jumlah hasil maksimal (default: 5)
 * 
 * Menggunakan Nominatim (OpenStreetMap) - Gratis, tidak perlu API key
 */

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

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 5;

if (empty($query)) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Query parameter "q" is required'
    ]);
    exit;
}

// Validate limit
if ($limit < 1 || $limit > 20) {
    $limit = 5;
}

// URL encode query
$encoded_query = urlencode($query);

// Nominatim API endpoint
$nominatim_url = "https://nominatim.openstreetmap.org/search?format=json&q={$encoded_query}&limit={$limit}&addressdetails=1";

// Setup cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $nominatim_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'WebGIS Pendidikan Lampung Selatan'); // Required by Nominatim
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

if ($curl_error) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Geocoding service error: ' . $curl_error
    ]);
    exit;
}

if ($http_code !== 200) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Geocoding service returned error code: ' . $http_code
    ]);
    exit;
}

$results = json_decode($response, true);

if (!$results) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Invalid response from geocoding service'
    ]);
    exit;
}

// Format response
$formatted_results = [];
foreach ($results as $result) {
    $formatted_results[] = [
        'display_name' => $result['display_name'],
        'latitude' => floatval($result['lat']),
        'longitude' => floatval($result['lon']),
        'type' => $result['type'] ?? '',
        'class' => $result['class'] ?? '',
        'address' => $result['address'] ?? []
    ];
}

echo json_encode([
    'success' => true,
    'query' => $query,
    'count' => count($formatted_results),
    'results' => $formatted_results
], JSON_NUMERIC_CHECK);
?>

