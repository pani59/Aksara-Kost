<?php
require_once '../database.php';

// Cek apakah ada ID yang dikirim via URL
if (!isset($_GET['id'])) {
    header("Location: booking.php"); // Sesuaikan dengan halaman asal tabelmu (misal: user.php, kos.php, dll)
    exit;
}

$id = (int) $_GET['id'];

// Langsung eksekusi hapus karena konfirmasi sudah dilakukan oleh SweetAlert2 di frontend
$hapus = mysqli_query(
    $conn,
    "DELETE FROM booking WHERE booking_id = $id" // Sesuaikan nama tabel dan kolom ID-nya
);

if (!$hapus) {
    die("Gagal menghapus data: " . mysqli_error($conn));
}

// Redirect kembali ke halaman tabel setelah berhasil
header("Location: booking.php");
exit;
?>