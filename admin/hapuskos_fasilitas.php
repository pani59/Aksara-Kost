<?php
require_once '../database.php';

// Cek apakah parameter kos_id dan fasilitas_id tersedia di URL
if (!isset($_GET['kos_id']) || !isset($_GET['fasilitas_id'])) {
    header("Location: kos_fasilitas.php");
    exit;
}

$kos_id = (int) $_GET['kos_id'];
$fasilitas_id = (int) $_GET['fasilitas_id'];

// ==========================================
// AMBIL DATA KOST FASILITAS (VALIDASI)
// ==========================================
$query = mysqli_query(
    $conn,
    "SELECT * FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($query);

// Jika data relasi tidak ditemukan di database
if (!$data) {
    header("Location: kos_fasilitas.php");
    exit;
}

// ==========================================
// PROSES HAPUS LANGSUNG
// ==========================================
$hapus = mysqli_query(
    $conn,
    "DELETE FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id"
);

if (!$hapus) {
    die("Gagal menghapus kos fasilitas: " . mysqli_error($conn));
}

// Kembali ke halaman kos fasilitas setelah berhasil
header("Location: kos_fasilitas.php");
exit;
?>