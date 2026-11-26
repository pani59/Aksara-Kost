<?php
require_once '../database.php';

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    header("Location: review.php");
    exit;
}

$id = (int) $_GET['id'];

// ==========================================
// AMBIL DATA REVIEW (VALIDASI)
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
// PROSES HAPUS LANGSUNG
// ==========================================
$hapus = mysqli_query(
    $conn,
    "DELETE FROM review WHERE review_id = $id"
);

if (!$hapus) {
    die("Gagal menghapus review: " . mysqli_error($conn));
}

// Kembali ke halaman review setelah berhasil
header("Location: review.php");
exit;
?>