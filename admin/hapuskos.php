<?php

require_once '../database.php';

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    header("Location: kos.php");
    exit;
}

$id = (int) $_GET['id'];

// ==========================================
// AMBIL DATA KOS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM kos WHERE kos_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$kos = mysqli_fetch_assoc($query);

// Jika data kos tidak ditemukan
if (!$kos) {
    header("Location: kos.php");
    exit;
}

// ==========================================
// HAPUS DATA KOS
// ==========================================

$hapus = mysqli_query(
    $conn,
    "DELETE FROM kos WHERE kos_id = $id"
);

if (!$hapus) {
    die("Gagal menghapus kos: " . mysqli_error($conn));
}

// ==========================================
// KEMBALI KE HALAMAN KOS
// ==========================================

header("Location: kos.php");
exit;

?>