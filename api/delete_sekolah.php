<?php
/**
 * API Endpoint: Delete Sekolah (Titik Digitasi)
 * Method: DELETE
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

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

// Check if sekolah exists dan ambil kecamatan
$check_query = "SELECT id, nama_sekolah, kecamatan FROM sekolah WHERE id = $id";
$check_result = mysqli_query($conn, $check_query);

if (!$check_result || mysqli_num_rows($check_result) === 0) {
    ob_end_clean();
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'message' => 'Sekolah tidak ditemukan'
    ]);
    mysqli_close($conn);
    exit;
}

$row = mysqli_fetch_assoc($check_result);
$nama_sekolah = $row['nama_sekolah'];
$kecamatan = $row['kecamatan'] ? mysqli_real_escape_string($conn, $row['kecamatan']) : '';

// Delete from database
$query = "DELETE FROM sekolah WHERE id = $id";
$result = mysqli_query($conn, $query);

ob_end_clean();

if ($result) {
    // Update jumlah sekolah di kecamatan_analisis (kurangi 1)
    if (!empty($kecamatan)) {
        $kecamatan_escaped = mysqli_real_escape_string($conn, $kecamatan);
        $update_query = "UPDATE kecamatan_analisis SET jumlah_sekolah = GREATEST(jumlah_sekolah - 1, 0) WHERE nama_kecamatan = '$kecamatan_escaped'";
        $update_result = mysqli_query($conn, $update_query);
        if (!$update_result) {
            error_log("Failed to update kecamatan_analisis: " . mysqli_error($conn));
        }
    }
    
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

mysqli_close($conn);
exit;
?>
