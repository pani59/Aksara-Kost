<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID KOS
// ==========================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Kos tidak ditemukan.");
}

$kos_id = intval($_GET['id']);

// Ambil data kos untuk ditampilkan di pesan konfirmasi (opsional)
$query_cek = mysqli_query($conn, "SELECT * FROM kos WHERE kos_id = $kos_id");
if (!$query_cek || mysqli_num_rows($query_cek) == 0) {
    header("Location: kosowner.php");
    exit;
}
$kos = mysqli_fetch_assoc($query_cek);

// ==========================================
// PROSES HAPUS KETIKA DIKONFIRMASI
// ==========================================
if (isset($_POST['konfirmasi_hapus'])) {
    $query  = "DELETE FROM kos WHERE kos_id = $kos_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        // Arahkan kembali ke halaman daftar kos owner setelah berhasil dihapus
        header("Location: kosowner.php");
        exit;
    } else {
        die("Gagal menghapus kos: " . mysqli_error($conn));
    }
}
?>