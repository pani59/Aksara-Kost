<?php
require_once __DIR__ . '/database.php';

echo "<h3>Pengecekan Variabel & Koneksi Database:</h3>";
echo "Host: " . ($host ?? 'Belum diset') . "<br>";
echo "User: " . ($user ?? 'Belum diset') . "<br>";
echo "Database: " . ($db ?? 'Belum diset') . "<br>";
echo "Port: " . ($port ?? 'Belum diset') . "<br><br>";

if (isset($conn) && $conn) {
    echo "<b style='color:green;'>Status: Koneksi database BERHASIL!</b>";
} else {
    echo "<b style='color:red;'>Status: Koneksi database GAGAL!</b>";
}
?>