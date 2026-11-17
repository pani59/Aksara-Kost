<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ambil konfigurasi dari Environment Variables Railway, atau gunakan localhost jika di XAMPP lokal
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'aksara_kost';
$port = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

// Khusus Railway, terkadang host dan port digabung atau memerlukan penanganan khusus,
// mari kita gunakan mysqli_connect dengan parameter port secara terpisah:
$conn = @mysqli_connect($host, $user, $pass, $db, $port);

// Buat alias $db
$db = $conn;

// Cek jika koneksi gagal
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error() . " (Host: $host, Port: $port)");
}
?>