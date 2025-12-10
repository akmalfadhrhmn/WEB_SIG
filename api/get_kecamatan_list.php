<?php
/**
 * API Endpoint: Get List Kecamatan (untuk dropdown)
 * Returns simple list of kecamatan names
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/database.php';

$query = "SELECT DISTINCT nama_kecamatan FROM kecamatan ORDER BY nama_kecamatan";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => "Query error: " . mysqli_error($conn)
    ]);
    exit;
}

$kecamatan_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kecamatan_list[] = $row['nama_kecamatan'];
}

echo json_encode([
    'success' => true,
    'kecamatan' => $kecamatan_list
]);

mysqli_close($conn);
?>

