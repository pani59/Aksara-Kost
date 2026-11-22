<?php
include '../database.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($booking_id <= 0) { 
    header("Location: booking_owner.php"); 
    exit; 
}

$query_booking = mysqli_query($conn, "SELECT b.*, u.nama AS nama_user, k.no_kamar, kos.nama_kos 
                                      FROM booking b
                                      JOIN user u ON b.user_id = u.user_id
                                      JOIN kamar k ON b.kamar_id = k.kamar_id
                                      JOIN kos ON k.kos_id = kos.kos_id
                                      WHERE b.booking_id = '$booking_id'");
$booking = mysqli_fetch_assoc($query_booking);

if (!$booking) { 
    die("Data booking tidak ditemukan."); 
}

// Hanya memproses dan mengubah status_booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_booking = mysqli_real_escape_string($conn, $_POST['status_booking']);
    $kamar_id = $booking['kamar_id']; // Ambil kamar_id dari data booking saat ini

    // 1. Update status booking
    $update = "UPDATE booking SET status_booking = '$status_booking' WHERE booking_id = '$booking_id'";
    if (mysqli_query($conn, $update)) {
        
        // 2. OTOMATIS UPDATE STATUS KAMAR BERDASARKAN STATUS BOOKING
        if ($status_booking === 'Disetujui') {
            // Jika disetujui, ubah kamar menjadi 'Terisi'
            mysqli_query($conn, "UPDATE kamar SET status_kamar = 'Terisi' WHERE kamar_id = '$kamar_id'");
        } else {
            // Jika ditolak atau dikembalikan jadi menunggu, ubah kamar jadi 'Kosong'
            mysqli_query($conn, "UPDATE kamar SET status_kamar = 'Kosong' WHERE kamar_id = '$kamar_id'");
        }

        header("Location: booking_owner.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Booking (Owner) - AKSARA KOST</title>
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
        .custom-input-group .form-select {
            border: none;
            background: transparent;
            box-shadow: none;
            padding-left: 14px;
            font-size: 0.95rem;
            color: #334155;
            width: 100%;
        }

        .custom-input-group .form-control[readonly] {
            background-color: transparent;
            color: #64748b;
        }

        /* Tombol Kembali & Simpan */
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

        .btn-simpan {
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

        .btn-simpan:hover {
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
                <i class="bi bi-pencil-fill fs-5"></i> Edit Data Booking (Owner)
            </h1>
            <p class="header-subtitle">Silahkan perbarui data booking di bawah ini.</p>
        </div>
        <div class="header-logo">
            <img src="../assets/img/logoaksara.png" alt="Logo Aksara Kost">
        </div>
    </div>

    <!-- Container Form Utama -->
    <div class="form-card">
        <div class="row align-items-center">
            
            <!-- Sisi Kiri: Logo Aksara (Pengganti Gambar Rumah) -->
            <div class="col-md-4">
                <div class="left-info-panel">
                    <div class="logo-circle-bg">
                        <img src="../assets/img/logoaksara.png" alt="Logo Aksara Kost">
                    </div>
                    <h2 class="left-info-title">Data Booking</h2>
                    <p class="left-info-desc">Perbarui status pemesanan atau survey kamar dengan benar.</p>
                    <div class="divider-home">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Input Form -->
            <div class="col-md-8 ps-md-4">
                <form method="POST">
                    
                    <!-- NAMA PEMESAN -->
                    <div class="mb-3">
                        <label class="form-label">Nama Pemesan</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($booking['nama_user']); ?>" readonly>
                        </div>
                    </div>

                    <!-- KOST & KAMAR -->
                    <div class="mb-3">
                        <label class="form-label">Kost & Kamar</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-building-fill"></i>
                            </div>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($booking['nama_kos']); ?> - Kamar <?= htmlspecialchars($booking['no_kamar']); ?>" readonly>
                        </div>
                    </div>

                    <!-- TANGGAL SURVEY -->
                    <div class="mb-3">
                        <label class="form-label">Tanggal Survey</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-calendar-event-fill"></i>
                            </div>
                            <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($booking['tanggal_survey'])); ?>" readonly>
                        </div>
                    </div>

                    <!-- STATUS BOOKING -->
                    <div class="mb-4">
                        <label class="form-label">Status Booking</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <select name="status_booking" class="form-select" required>
                                <option value="Menunggu" <?= $booking['status_booking'] === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="Disetujui" <?= $booking['status_booking'] === 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="Ditolak" <?= $booking['status_booking'] === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI -->
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="booking_owner.php" class="btn-kembali">
                            <i class="bi bi-arrow-counterclockwise"></i> Kembali
                        </a>
                        <button type="submit" class="btn-simpan">
                            <i class="bi bi-check-lg"></i> Simpan
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

</div>

</body>
</html>