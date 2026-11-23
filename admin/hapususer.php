<?php
require_once '../database.php';

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    header("Location: user.php");
    exit;
}

$id = (int) $_GET['id'];

// ==========================================
// AMBIL DATA USER
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM user WHERE user_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($query);

// Jika user tidak ditemukan
if (!$user) {
    header("Location: user.php");
    exit;
}

// ==========================================
// PROSES HAPUS KETIKA DIKONFIRMASI
// ==========================================
if (isset($_POST['konfirmasi_hapus'])) {
    $hapus = mysqli_query(
        $conn,
        "DELETE FROM user WHERE user_id = $id"
    );

    if (!$hapus) {
        die("Gagal menghapus user: " . mysqli_error($conn));
    }

    // Kembali ke halaman user setelah berhasil
    header("Location: user.php");
    exit;
}
?>