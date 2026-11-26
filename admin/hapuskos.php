<?php
require_once '../database.php';

// Cek apakah ada parameter ID kos yang dikirim via URL
if (!isset($_GET['id'])) {
    header("Location: kos.php"); 
    exit;
}

$id = (int) $_GET['id'];

// ==========================================
// AMBIL DATA KOS (VALIDASI)
// ==========================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM kos WHERE kos_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$kos = mysqli_fetch_assoc($query);

// Jika data kos tidak ditemukan di database
if (!$kos) {
    header("Location: kos.php");
    exit;
}

// ==========================================
// PROSES HAPUS LANGSUNG
// ==========================================
$hapus = mysqli_query(
    $conn,
    "DELETE FROM kos WHERE kos_id = $id"
);

if (!$hapus) {
    die("Gagal menghapus kos: " . mysqli_error($conn));
}

// Kembali ke halaman kos admin setelah berhasil
header("Location: kos.php"); 
exit;
?>