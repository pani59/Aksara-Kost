<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER REVIEW_ID
// ==========================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Review tidak ditemukan.");
}

$review_id = intval($_GET['id']);

// Ambil data review untuk ditampilkan di pesan konfirmasi (opsional)
$query_cek = mysqli_query($conn, "SELECT * FROM review WHERE review_id = $review_id");
if (!$query_cek || mysqli_num_rows($query_cek) == 0) {
    header("Location: review_owner.php");
    exit;
}
$review = mysqli_fetch_assoc($query_cek);

// ==========================================
// PROSES HAPUS KETIKA DIKONFIRMASI
// ==========================================
if (isset($_POST['konfirmasi_hapus'])) {
    $query  = "DELETE FROM review WHERE review_id = $review_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        // Redirect kembali ke halaman daftar review owner setelah berhasil
        header("Location: review_owner.php");
        exit;
    } else {
        die("Gagal menghapus data review: " . mysqli_error($conn));
    }
}
?>