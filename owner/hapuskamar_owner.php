<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID KAMAR
// ==========================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: kamar_owner.php");
    exit;
}

$kamar_id = intval($_GET['id']);

// Ambil data kamar untuk validasi keberadaan data di database
$query_cek = mysqli_query($conn, "SELECT * FROM kamar WHERE kamar_id = $kamar_id");
if (!$query_cek || mysqli_num_rows($query_cek) == 0) {
    header("Location: kamar_owner.php");
    exit;
}
$kamar = mysqli_fetch_assoc($query_cek);

// ==========================================
// PROSES HAPUS LANGSUNG (TANPA POST)
// ==========================================
$query  = "DELETE FROM kamar WHERE kamar_id = $kamar_id";
$delete = mysqli_query($conn, $query);

if ($delete) {
    header("Location: kamar_owner.php");
    exit;
} else {
    die("Gagal menghapus kamar: " . mysqli_error($conn));
}
?>