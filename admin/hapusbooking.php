<?php
require_once '../database.php';

// Cek apakah ada ID booking yang dikirim via URL
if (!isset($_GET['id'])) {
    header("Location: booking.php");
    exit;
}

$id = (int) $_GET['id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM booking WHERE booking_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$booking = mysqli_fetch_assoc($query);

// Jika data booking tidak ditemukan di database
if (!$booking) {
    header("Location: booking.php");
    exit;
}

// ==========================================
// PROSES HAPUS DATA KETIKA DIKONFIRMASI
// ==========================================
if (isset($_POST['konfirmasi_hapus'])) {
    $hapus = mysqli_query(
        $conn,
        "DELETE FROM booking WHERE booking_id = $id"
    );

    if (!$hapus) {
        die("Gagal menghapus booking: " . mysqli_error($conn));
    }

    // Kembali ke halaman kelola booking setelah berhasil
    header("Location: booking.php");
    exit;
}
?>

