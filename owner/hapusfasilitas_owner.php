<?php
session_start();
require_once '../database.php';

// ==========================================
// CEK PARAMETER KOS_ID & FASILITAS_ID
// ==========================================
if (isset($_GET['kos_id']) && isset($_GET['fasilitas_id'])) {
    
    $kos_id       = intval($_GET['kos_id']);
    $fasilitas_id = intval($_GET['fasilitas_id']);

    // ======================================
    // PROSES HAPUS DATA
    // ======================================
    $query  = "DELETE FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        // Redirect kembali ke halaman utama setelah berhasil
        header("Location: kosfasilitas_owner.php");
        exit;
    } else {
        die("Gagal menghapus data: " . mysqli_error($conn));
    }

} else {
    die("ID Kos atau ID Fasilitas tidak ditemukan.");
}
?>