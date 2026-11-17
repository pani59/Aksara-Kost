<?php
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID KOS
// ==========================================
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $kos_id = intval($_GET['id']);

    // ======================================
    // PROSES HAPUS KOS
    // ======================================
    $query  = "DELETE FROM kos WHERE kos_id = $kos_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        // Arahkan kembali ke halaman daftar kos owner setelah berhasil dihapus
        header("Location: kosowner.php");
        exit;
    } else {
        die("Gagal menghapus kos: " . mysqli_error($conn));
    }

} else {
    die("ID Kos tidak ditemukan.");
}
?>