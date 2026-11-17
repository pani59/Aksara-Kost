<?php
session_start();
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID BOOKING
// ==========================================
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $booking_id = intval($_GET['id']);

    // ======================================
    // PROSES HAPUS BOOKING
    // ======================================
    $query  = "DELETE FROM booking WHERE booking_id = $booking_id";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        header("Location: booking_owner.php");
        exit;
    } else {
        die("Gagal menghapus data booking: " . mysqli_error($conn));
    }

} else {
    die("ID Booking tidak ditemukan.");
}
?>