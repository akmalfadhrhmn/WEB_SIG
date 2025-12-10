<?php
/**
 * API Endpoint: Update Jumlah Sekolah di Kecamatan Analisis
 * Internal function - dipanggil setelah create/update/delete sekolah
 * 
 * Fungsi: Menghitung ulang jumlah sekolah per kecamatan dan update tabel kecamatan_analisis
 */

// Disable error display
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

require_once __DIR__ . '/../config/database.php';

if (!$conn) {
    ob_end_clean();
    return false;
}

// Hitung jumlah sekolah per kecamatan
$query = "SELECT kecamatan, COUNT(*) as jumlah 
          FROM sekolah 
          WHERE kecamatan IS NOT NULL AND kecamatan != ''
          GROUP BY kecamatan";

$result = mysqli_query($conn, $query);

if (!$result) {
    ob_end_clean();
    return false;
}

// Update setiap kecamatan di tabel kecamatan_analisis
while ($row = mysqli_fetch_assoc($result)) {
    $kecamatan = mysqli_real_escape_string($conn, $row['kecamatan']);
    $jumlah = intval($row['jumlah']);
    
    $update_query = "UPDATE kecamatan_analisis 
                     SET jumlah_sekolah = $jumlah 
                     WHERE nama_kecamatan = '$kecamatan'";
    
    mysqli_query($conn, $update_query);
}

ob_end_clean();
return true;
?>

