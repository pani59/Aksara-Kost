<?php
session_start();
include '../database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$session_owner_id = (int) $_SESSION['user_id'];

/* =====================================================
   AMBIL DATA FASILITAS DARI DATABASE
   ===================================================== */
$query_fasilitas = mysqli_query($conn, "SELECT * FROM fasilitas ORDER BY nama_fasilitas ASC");

/* =====================================================
   PROSES SIMPAN DATA KOST & FASILITAS
   ===================================================== */
if (isset($_POST['simpan'])) {

    $nama_kos          = mysqli_real_escape_string($conn, $_POST['nama_kos']);
    $alamat            = mysqli_real_escape_string($conn, $_POST['alamat']);
    $latitude          = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude         = mysqli_real_escape_string($conn, $_POST['longitude']);
    $tipe_kos          = mysqli_real_escape_string($conn, $_POST['tipe_kos']);
    $status_verifikasi = mysqli_real_escape_string($conn, $_POST['status_verifikasi']);

    /* =================================================
       1. SIMPAN KE DATABASE TABEL KOS
       ================================================= */
    $query = "INSERT INTO kos 
              (owner_id, nama_kos, alamat, latitude, longitude, tipe_kos, status_verifikasi)
              VALUES 
              ('$session_owner_id', '$nama_kos', '$alamat', '$latitude', '$longitude', '$tipe_kos', '$status_verifikasi')";

    if (mysqli_query($conn, $query)) {

        // Ambil kos_id yang baru saja otomatis terbuat (AUTO_INCREMENT)
        $kos_id_baru = mysqli_insert_id($conn);

        /* =================================================
           2. SIMPAN KE TABEL KOS_FASILITAS (JIKA ADA YANG DICENTANG)
           ================================================= */
        if (!empty($_POST['fasilitas'])) {
            foreach ($_POST['fasilitas'] as $fasilitas_id) {
                $fasilitas_id_clean = mysqli_real_escape_string($conn, $fasilitas_id);
                $query_relasi = "INSERT INTO kos_fasilitas (kos_id, fasilitas_id) 
                                 VALUES ('$kos_id_baru', '$fasilitas_id_clean')";
                mysqli_query($conn, $query_relasi);
            }
        }

        header("Location: kosowner.php");
        exit;

    } else {
        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Kost - AKSARA KOST</title>
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
        }

        .main-wrapper {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 15px;
        }

        /* Banner Header Atas */
        .header-banner {
            background: linear-gradient(135deg, #d8e5d8 0%, #e4ede4 60%, #d5e3d5 100%);
            border-radius: 16px;
            padding: 28px 36px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
            border: 1px solid #c9dac9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-banner::after {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.28);
            border-radius: 50%;
            top: -80px;
            right: 18%;
            pointer-events: none;
        }

        .header-title {
            font-weight: 700;
            font-size: 1.65rem;
            color: #1b3b2b;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header-subtitle {
            color: #52665a;
            margin-bottom: 0;
            font-size: 0.95rem;
            padding-left: 36px;
        }

        .header-logo img {
            height: 72px;
            width: auto;
            object-fit: contain;
            position: relative;
            z-index: 2;
        }

        /* Card Utama Form */
        .form-card {
            background-color: #ffffff;
            border-radius: 18px;
            border: 1px solid #e2e8e4;
            padding: 36px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        /* Kolom Kiri (Logo & Info Pengganti Gambar Rumah) */
        .left-info-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            height: 100%;
            padding: 20px;
            border-right: 1px solid #ebefec;
        }

        .logo-circle-bg {
            width: 190px;
            height: 190px;
            background-color: #eaf2eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            padding: 25px;
            position: relative;
        }

        .logo-circle-bg img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .left-info-title {
            font-weight: 700;
            color: #2c6e49;
            font-size: 1.2rem;
            margin-bottom: 8px;
        }

        .left-info-desc {
            color: #64748b;
            font-size: 0.9rem;
            max-width: 210px;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .divider-home {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 75%;
            gap: 12px;
            color: #6b8e78;
        }

        .divider-home::before,
        .divider-home::after {
            content: "";
            flex: 1;
            height: 1px;
            background-color: #cbd5ce;
        }

        /* Styling Input Form & Ikon di Dalamnya */
        .form-label {
            font-weight: 600;
            font-size: 0.92rem;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .custom-input-group {
            display: flex;
            align-items: center;
            background-color: #f8faf9;
            border: 1px solid #dce4df;
            border-radius: 10px;
            padding: 4px;
            transition: all 0.2s ease;
        }

        .custom-input-group:focus-within {
            border-color: #3a7d56;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(58, 125, 86, 0.12);
        }

        .input-icon-box {
            width: 38px;
            height: 38px;
            background-color: #e6efe8;
            color: #3a7d56;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
            margin-left: 2px;
        }

        .custom-input-group .form-control,
        .custom-input-group .form-select,
        .custom-input-group textarea {
            border: none;
            background: transparent;
            box-shadow: none;
            padding-left: 14px;
            font-size: 0.95rem;
            color: #334155;
            width: 100%;
        }

        .custom-input-group textarea {
            padding-top: 8px;
            padding-bottom: 8px;
            resize: vertical;
        }

        .custom-input-group .form-control::placeholder,
        .custom-input-group textarea::placeholder {
            color: #94a3b8;
        }

        /* Styling Box Checkbox Fasilitas */
        .fasilitas-container {
            background-color: #f8faf9;
            border: 1px solid #dce4df;
            border-radius: 10px;
            padding: 16px;
        }

        .fasilitas-item {
            background-color: #ffffff;
            border: 1px solid #e2e8e4;
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.2s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .fasilitas-item:hover {
            border-color: #3a7d56;
            background-color: #f2f8f4;
        }

        .fasilitas-item .form-check-input {
            margin-top: 0;
            cursor: pointer;
        }

        .fasilitas-item .form-check-input:checked {
            background-color: #3a7d56;
            border-color: #3a7d56;
        }

        .fasilitas-item label {
            cursor: pointer;
            font-size: 0.9rem;
            color: #334155;
            width: 100%;
            margin-bottom: 0;
            user-select: none;
        }

        /* Tombol Kembali & Tambah */
        .btn-kembali {
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #475569;
            border-radius: 50px;
            padding: 10px 26px;
            font-weight: 600;
            font-size: 0.92rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-kembali:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .btn-tambah {
            background-color: #4a8259;
            border: none;
            color: #ffffff;
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 600;
            font-size: 0.92rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
        }

        .btn-tambah:hover {
            background-color: #3b6b48;
            color: #ffffff;
        }

        /* Responsif untuk layar HP */
        @media (max-width: 768px) {
            .header-banner {
                flex-direction: column-reverse;
                text-align: center;
                gap: 15px;
            }
            .header-title {
                justify-content: center;
            }
            .header-subtitle {
                padding-left: 0;
            }
            .left-info-panel {
                border-right: none;
                border-bottom: 1px solid #ebefec;
                padding-bottom: 28px;
                margin-bottom: 28px;
            }
        }
    </style>
</head>

<body>

<div class="main-wrapper">
    
    <!-- Banner Header Atas -->
    <div class="header-banner">
        <div>
            <h1 class="header-title">
                <i class="bi bi-pencil-fill fs-5"></i> Tambah Data Kost
            </h1>
            <p class="header-subtitle">Silahkan tambah data kost di bawah ini.</p>
        </div>
        <div class="header-logo">
            <img src="../assets/img/logoaksara.png" alt="Logo Aksara Kost">
        </div>
    </div>

    <!-- Container Form Utama -->
    <div class="form-card">
        <div class="row align-items-start">
            
            <!-- Sisi Kiri: Logo Aksara (Pengganti Gambar Rumah) -->
            <div class="col-md-4">
                <div class="left-info-panel">
                    <div class="logo-circle-bg">
                        <img src="../assets/img/logoaksara.png" alt="Logo Aksara Kost">
                    </div>
                    <h2 class="left-info-title">Data Kost</h2>
                    <p class="left-info-desc">Lengkapi informasi properti kost dengan benar.</p>
                    <div class="divider-home">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Input Form -->
            <div class="col-md-8 ps-md-4">
                <form method="POST">

                    <!-- NAMA KOS -->
                    <div class="mb-3">
                        <label class="form-label">Nama Kost</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-building-fill"></i>
                            </div>
                            <input type="text" name="nama_kos" class="form-control" placeholder="Masukkan nama kost" required>
                        </div>
                    </div>

                    <!-- ALAMAT -->
                    <div class="mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <div class="custom-input-group align-items-start">
                            <div class="input-icon-box mt-1">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap kost" required></textarea>
                        </div>
                    </div>

                    <!-- LATITUDE -->
                    <div class="mb-3">
                        <label class="form-label">Latitude</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-compass-fill"></i>
                            </div>
                            <input type="text" name="latitude" class="form-control" placeholder="Contoh: -6.200000">
                        </div>
                    </div>

                    <!-- LONGITUDE -->
                    <div class="mb-3">
                        <label class="form-label">Longitude</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-compass"></i>
                            </div>
                            <input type="text" name="longitude" class="form-control" placeholder="Contoh: 106.816666">
                        </div>
                    </div>

                    <!-- TIPE KOS -->
                    <div class="mb-3">
                        <label class="form-label">Tipe Kost</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-tags-fill"></i>
                            </div>
                            <select name="tipe_kos" class="form-select" required>
                                <option value="">-- Pilih Tipe Kost --</option>
                                <option value="Putra">Putra</option>
                                <option value="Putri">Putri</option>
                                <option value="Campur">Campur</option>
                            </select>
                        </div>
                    </div>

                    <!-- STATUS VERIFIKASI -->
                    <div class="mb-3">
                        <label class="form-label">Status Verifikasi</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <select name="status_verifikasi" class="form-select" required>
                                <option value="Menunggu">Menunggu</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Non-aktif">Non-aktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- PILIH FASILITAS KOST (CHECKBOX) -->
                    <div class="mb-4">
                        <label class="form-label">Fasilitas Kost</label>
                        <div class="fasilitas-container">
                            <?php if (mysqli_num_rows($query_fasilitas) > 0) : ?>
                                <div class="row g-2">
                                    <?php while ($fas = mysqli_fetch_assoc($query_fasilitas)) : ?>
                                        <div class="col-sm-6 col-md-4">
                                            <div class="fasilitas-item">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="fasilitas[]" 
                                                       value="<?= $fas['fasilitas_id']; ?>" 
                                                       id="fas_<?= $fas['fasilitas_id']; ?>">
                                                <label for="fas_<?= $fas['fasilitas_id']; ?>">
                                                    <?= htmlspecialchars($fas['nama_fasilitas']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else : ?>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-info-circle"></i> Belum ada data fasilitas yang ditambahkan admin.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI -->
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="kosowner.php" class="btn-kembali">
                            <i class="bi bi-arrow-counterclockwise"></i> Kembali
                        </a>
                        <button type="submit" name="simpan" class="btn-tambah">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

</div>

</body>
</html>