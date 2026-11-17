<?php

require_once '../database.php';

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    header("Location: fasilitas.php");
    exit;
}

$id = (int) $_GET['id'];

// ==========================================
// AMBIL DATA FASILITAS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM fasilitas WHERE fasilitas_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$fasilitas = mysqli_fetch_assoc($query);

// Jika fasilitas tidak ditemukan
if (!$fasilitas) {
    header("Location: fasilitas.php");
    exit;
}

// ==========================================
// HAPUS DATA FASILITAS
// ==========================================

$hapus = mysqli_query(
    $conn,
    "DELETE FROM fasilitas WHERE fasilitas_id = $id"
);

if (!$hapus) {
    die("Gagal menghapus fasilitas: " . mysqli_error($conn));
}

// ==========================================
// KEMBALI KE HALAMAN FASILITAS
// ==========================================

header("Location: fasilitas.php");
exit;

?>