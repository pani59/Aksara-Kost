<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID KAMAR
// ==========================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Kamar tidak ditemukan.");
}

$kamar_id = intval($_GET['id']);

// Ambil data kamar untuk ditampilkan di pesan konfirmasi (opsional)
$query_cek = mysqli_query($conn, "SELECT * FROM kamar WHERE kamar_id = $kamar_id");
if (!$query_cek || mysqli_num_rows($query_cek) == 0) {
    header("Location: kamar_owner.php");
    exit;
}
$kamar = mysqli_fetch_assoc($query_cek);

// ==========================================
// PROSES HAPUS KETIKA DIKONFIRMASI
// ==========================================
if (isset($_POST['konfirmasi_hapus'])) {
    $query  = "DELETE FROM kamar WHERE kamar_id = $kamar_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        header("Location: kamar_owner.php");
        exit;
    } else {
        die("Gagal menghapus kamar: " . mysqli_error($conn));
    }
}
?>