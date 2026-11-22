<?php
require_once '../database.php';

// Cek apakah ada parameter kos_id dan fasilitas_id
if (!isset($_GET['kos_id']) || !isset($_GET['fasilitas_id'])) {
    header("Location: kos_fasilitas.php");
    exit;
}

$kos_id = (int) $_GET['kos_id'];
$fasilitas_id = (int) $_GET['fasilitas_id'];

// ==========================================
// AMBIL DATA KOST FASILITAS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($conn));
}

$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    header("Location: kos_fasilitas.php");
    exit;
}

// ==========================================
// PROSES HAPUS KETIKA DIKONFIRMASI
// ==========================================
if (isset($_POST['konfirmasi_hapus'])) {
    $hapus = mysqli_query(
        $conn,
        "DELETE FROM kos_fasilitas WHERE kos_id = $kos_id AND fasilitas_id = $fasilitas_id"
    );

    if (!$hapus) {
        die("Gagal menghapus kos fasilitas: " . mysqli_error($conn));
    }

    // Kembali ke halaman kos fasilitas setelah berhasil
    header("Location: kos_fasilitas.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Hapus Kost Fasilitas - AKSARA KOST</title>
    <link rel="icon" href="../assets/img/logoaksara.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f6f7f5;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Modal Kustom ala Gambar Desain */
        .delete-modal-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8e4;
            padding: 40px 30px;
            width: 100%;
            max-width: 440px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .trash-icon-container {
            width: 76px;
            height: 76px;
            background-color: #fdf2f2;
            color: #e05252;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px auto;
        }

        .delete-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .delete-desc {
            font-size: 0.92rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 30px;
        }

        .modal-btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-batal {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 600;
            font-size: 0.92rem;
            text-decoration: none;
            transition: 0.2s;
            flex: 1;
        }

        .btn-batal:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .btn-ya-hapus {
            background-color: #d9534f;
            border: none;
            color: #ffffff;
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 600;
            font-size: 0.92rem;
            transition: 0.2s;
            flex: 1;
        }

        .btn-ya-hapus:hover {
            background-color: #c9302c;
            color: #ffffff;
        }
    </style>
</head>

<body>

    <div class="delete-modal-card">
        <div class="trash-icon-container">
            <i class="bi bi-trash3"></i>
        </div>
        
        <h2 class="delete-title">Hapus Relasi Fasilitas?</h2>
        
        <p class="delete-desc">
            Apakah kamu yakin ingin menghapus data relasi kost dan fasilitas ini?<br>
            Data yang telah dihapus tidak dapat dikembalikan.
        </p>

        <form method="POST">
            <div class="modal-btn-group">
                <a href="kos_fasilitas.php" class="btn-batal">
                    Batal
                </a>
                <button type="submit" name="konfirmasi_hapus" class="btn-ya-hapus">
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>

</body>
</html>