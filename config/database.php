<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "webgis_pendidikan";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");
?>

