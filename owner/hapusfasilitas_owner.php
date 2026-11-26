<?php
session_start();
require_once '../database.php';

// ==========================================
// CEK PARAMETER KOS_ID & FASILITAS_ID
// ==========================================
if (!isset($_GET['kos_id']) || !isset($_GET['fasilitas_id'])) {
    header("Location: kos_fasilitas_owner.php"); // Sesuaikan dengan nama file tabel owner kamu
    exit;
}

$kos_id       = intval($_GET['kos_id']);
$fasilitas_id = intval($_GET['fasilitas_id']);

// Ambil data untuk pengecekan validasi keberadaan data di database
$query_cek = mysqli_query($conn, "SELECT * FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id");
if (!$query_cek || mysqli_num_rows($query_cek) == 0) {
    header("Location: kos_fasilitas_owner.php");
    exit;
}

// ==========================================
// PROSES HAPUS LANGSUNG (TANPA POST)
// ==========================================
$query  = "DELETE FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id";
$delete = mysqli_query($conn, $query);

if ($delete) {
    // Redirect kembali ke halaman utama setelah berhasil
    header("Location: kos_fasilitas_owner.php");
    exit;
} else {
    die("Gagal menghapus data: " . mysqli_error($conn));
}
?>