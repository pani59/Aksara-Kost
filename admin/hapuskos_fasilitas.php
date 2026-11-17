<?php

require_once '../database.php';

// Cek apakah ada parameter kos_id dan fasilitas_id
if (!isset($_GET['kos_id']) || !isset($_GET['fasilitas_id'])) {
    header("Location: kos_fasilitas.php");
    exit;
}

$kos_id = (int) $_GET['kos_id'];
$fasilitas_id = (int) $_GET['fasilitas_id'];

// ==========================================
// AMBIL DATA KOST FASILITAS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    header("Location: kos_fasilitas.php");
    exit;
}

// ==========================================
// HAPUS DATA KOST FASILITAS
// ==========================================

$hapus = mysqli_query(
    $conn,
    "DELETE FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id"
);

if (!$hapus) {
    die("Gagal menghapus kos fasilitas: " . mysqli_error($conn));
}

// ==========================================
// KEMBALI KE HALAMAN KOST FASILITAS
// ==========================================

header("Location: kos_fasilitas.php");
exit;

?>