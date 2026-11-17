<?php
// Aktifkan error reporting untuk debugging jika masih ada kendala
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cek apakah aplikasi berjalan di Railway atau di Localhost (XAMPP)
if (getenv('MYSQLHOST') || getenv('DB_HOST')) {
    // Konfigurasi untuk Railway (mengambil dari Environment Variables Railway)
    $host = getenv('MYSQLHOST') ?: getenv('DB_HOST');
    $user = getenv('MYSQLUSER') ?: getenv('DB_USER');
    $pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD');
    $db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME');
    $port = getenv('MYSQLPORT') ?: getenv('DB_PORT', 3306);
} else {
    // Konfigurasi untuk Localhost (XAMPP kamu di komputer)
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'aksara_kost'; // Sesuaikan dengan nama database XAMPP kamu
    $port = 3306;
}

// Lakukan koneksi menggunakan MySQLi
$conn = mysqli_connect($host, $user, $pass, $db, $port);

// Buat alias $db agar kompatibel dengan file lain yang menggunakan variabel $db
$db = $conn;

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>