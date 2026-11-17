<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID KAMAR
// ==========================================
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $kamar_id = intval($_GET['id']);

    // ======================================
    // PROSES HAPUS KAMAR
    // ======================================
    $query  = "DELETE FROM kamar WHERE kamar_id = $kamar_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        header("Location: kamar_owner.php");
        exit;
    } else {
        die("Gagal menghapus kamar: " . mysqli_error($conn));
    }

} else {
    die("ID Kamar tidak ditemukan.");
}
?>