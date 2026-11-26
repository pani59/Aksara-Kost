<?php
// Hubungkan ke database
require_once '../database.php';

// Cek apakah ada ID yang dikirim via URL
if (!isset($_GET['id'])) {
    header("Location: foto_kos.php");
    exit;
}

$id = (int) $_GET['id'];

// Ambil data foto kos untuk mendapatkan nama file fisik di folder
$query = mysqli_query(
    $conn,
    "SELECT * FROM foto_kos WHERE foto_id = $id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$foto = mysqli_fetch_assoc($query);

// Jika data foto tidak ditemukan di database
if (!$foto) {
    header("Location: foto_kos.php");
    exit;
}

// Hapus file gambar fisik dari folder uploads jika ada
if (!empty($foto['path_foto'])) {
    $file_path = '../uploads/' . $foto['path_foto'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Hapus data foto kos dari database
$hapus = mysqli_query(
    $conn,
    "DELETE FROM foto_kos WHERE foto_id = $id"
);

if (!$hapus) {
    die("Gagal menghapus foto kos: " . mysqli_error($conn));
}

// Redirect kembali ke halaman foto kos setelah berhasil
header("Location: foto_kos.php");
exit;
?>