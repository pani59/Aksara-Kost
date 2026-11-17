<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ambil konfigurasi dari Environment Variables Railway
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST');
$user = getenv('MYSQLUSER') ?: getenv('DB_USER');
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD');
$db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME');
$port = (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);

// Validasi jika variabel Railway belum terdeteksi sama sekali
if (!$host) {
    // Fallback jika berjalan di lokal XAMPP
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'aksara_kost';
    $port = 3306;
}

// Lakukan koneksi database
$conn = @mysqli_connect($host, $user, $pass, $db, $port);

// Buat alias $db
$db = $conn;

// Cek jika koneksi gagal
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error() . " (Host Terdeteksi: $host, Port: $port)");
}
?>