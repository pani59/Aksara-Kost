<?php

require_once '../database.php';

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    header("Location: kamar.php");
    exit;
}

$id = (int) $_GET['id'];

// ==========================================
// AMBIL DATA KAMAR
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM kamar WHERE kamar_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$kamar = mysqli_fetch_assoc($query);

// Jika kamar tidak ditemukan
if (!$kamar) {
    header("Location: kamar.php");
    exit;
}

// ==========================================
// HAPUS DATA KAMAR
// ==========================================

$hapus = mysqli_query(
    $conn,
    "DELETE FROM kamar WHERE kamar_id = $id"
);

if (!$hapus) {
    die("Gagal menghapus kamar: " . mysqli_error($conn));
}

// ==========================================
// KEMBALI KE HALAMAN KAMAR
// ==========================================

header("Location: kamar.php");
exit;

?>