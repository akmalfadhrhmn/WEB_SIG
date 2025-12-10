<?php
/**
 * API Endpoint: Delete Sekolah (Titik Digitasi)
 * Method: DELETE
 * 
 * Query Parameters:
 * - id: ID sekolah yang akan dihapus
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'error' => true,
        'message' => 'Method not allowed. Use DELETE.'
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

// Check if sekolah exists
$check_query = "SELECT id, nama_sekolah FROM sekolah WHERE id = ?";
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

$row = mysqli_fetch_assoc($result);
$nama_sekolah = $row['nama_sekolah'];
mysqli_stmt_close($check_stmt);

// Delete from database
$query = "DELETE FROM sekolah WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Prepare statement failed: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Sekolah berhasil dihapus',
        'deleted_id' => $id,
        'deleted_nama' => $nama_sekolah
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Failed to delete: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

