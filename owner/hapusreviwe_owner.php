<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER REVIEW_ID
// ==========================================
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $review_id = intval($_GET['id']);

    // ======================================
    // PROSES HAPUS DATA REVIEW
    // ======================================
    $query  = "DELETE FROM review WHERE review_id = $review_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        // Redirect kembali ke halaman daftar review owner setelah berhasil
        header("Location: review_owner.php");
        exit;
    } else {
        die("Gagal menghapus data review: " . mysqli_error($conn));
    }

} else {
    die("ID Review tidak ditemukan.");
}
?>