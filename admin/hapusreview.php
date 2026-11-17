<?php

require_once '../database.php';

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    header("Location: review.php");
    exit;
}

$id = (int) $_GET['id'];

// ==========================================
// AMBIL DATA REVIEW
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM review WHERE review_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$review = mysqli_fetch_assoc($query);

// Jika review tidak ditemukan
if (!$review) {
    header("Location: review.php");
    exit;
}

// ==========================================
// HAPUS DATA REVIEW
// ==========================================

$hapus = mysqli_query(
    $conn,
    "DELETE FROM review WHERE review_id = $id"
);

if (!$hapus) {
    die("Gagal menghapus review: " . mysqli_error($conn));
}

// ==========================================
// KEMBALI KE HALAMAN REVIEW
// ==========================================

header("Location: review.php");
exit;

?>