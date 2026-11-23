<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID BOOKING
// ==========================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Booking tidak ditemukan.");
}

$booking_id = intval($_GET['id']);

// Ambil data booking untuk ditampilkan di pesan konfirmasi (opsional)
$query_cek = mysqli_query($conn, "SELECT * FROM booking WHERE booking_id = $booking_id");
if (!$query_cek || mysqli_num_rows($query_cek) == 0) {
    header("Location: booking_owner.php");
    exit;
}
$booking = mysqli_fetch_assoc($query_cek);

// ==========================================
// PROSES HAPUS KETIKA DIKONFIRMASI
// ==========================================
if (isset($_POST['konfirmasi_hapus'])) {
    $query  = "DELETE FROM booking WHERE booking_id = $booking_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        header("Location: booking_owner.php");
        exit;
    } else {
        die("Gagal menghapus data booking: " . mysqli_error($conn));
    }
}
?>