<?php
// Hubungkan ke database (sesuaikan path-nya jika berbeda)
require_once '../database.php';

// Cek apakah parameter ID tersedia di URL
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Eksekusi query hapus data berdasarkan ID
    $query = mysqli_query($conn, "DELETE FROM fasilitas WHERE fasilitas_id = $id");

    if (!$query) {
        die("Gagal menghapus fasilitas: " . mysqli_error($conn));
    }
}

// Redirect kembali ke halaman data fasilitas setelah selesai
header("Location: fasilitas.php");
exit;
?>