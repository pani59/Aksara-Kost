<?php

require_once '../database.php';

// Cek apakah ada ID
if (!isset($_GET['id'])) {
    header("Location: fotokos_owner.php");
    exit;
}

$id = (int) $_GET['id'];

// ==========================================
// AMBIL DATA FOTO KOS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM foto_kos WHERE foto_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$foto = mysqli_fetch_assoc($query);

// Jika foto tidak ditemukan
if (!$foto) {
    header("Location: fotokos_owner.php");
    exit;
}

// ==========================================
// HAPUS FILE GAMBAR DARI FOLDER UPLOADS
// ==========================================

if (!empty($foto['path_foto'])) {
    $file_path = '../uploads/' . $foto['path_foto'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// ==========================================
// HAPUS DATA FOTO KOS DARI DATABASE
// ==========================================

$hapus = mysqli_query(
    $conn,
    "DELETE FROM foto_kos WHERE foto_id = $id"
);

if (!$hapus) {
    die("Gagal menghapus foto kos: " . mysqli_error($conn));
}

// ==========================================
// KEMBALI KE HALAMAN FOTO KOS
// ==========================================

header("Location: fotokos_owner.php");
exit;

?>