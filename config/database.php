<?php
// Disable error display
error_reporting(E_ALL);
ini_set('display_errors', 0);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webgis_pendidikan";

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Jangan gunakan die() karena akan output HTML
// Biarkan caller yang handle error
if (!$conn) {
    // Connection failed, $conn akan false
    // Caller harus check if (!$conn)
}

// Set charset hanya jika connection berhasil
if ($conn) {
    mysqli_set_charset($conn, "utf8mb4");
}
?>

