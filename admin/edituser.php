<?php
include '../database.php';

// ==========================================
// CEK ID USER
// ==========================================

if (!isset($_GET['id'])) {
    die("ID user tidak ditemukan.");
}

$id = intval($_GET['id']);


// ==========================================
// AMBIL DATA USER
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM user WHERE user_id = $id"
);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($query);

if (!$user) {
    die("Data user tidak ditemukan.");
}


// ==========================================
// PROSES SIMPAN UPDATE
// ==========================================

if (isset($_POST['simpan'])) {

    $nama              = mysqli_real_escape_string($conn, $_POST['nama']);
    $email             = mysqli_real_escape_string($conn, $_POST['email']);
    $password          = mysqli_real_escape_string($conn, $_POST['password']);
    $role              = mysqli_real_escape_string($conn, $_POST['role']);
    $no_wa             = mysqli_real_escape_string($conn, $_POST['no_wa']);
    $status_verifikasi = mysqli_real_escape_string($conn, $_POST['status_verifikasi']);


    // ======================================
    // UPDATE DATA USER
    // ======================================

    $update = mysqli_query(
        $conn,
        "UPDATE user SET
            nama = '$nama',
            email = '$email',
            password = '$password',
            role = '$role',
            no_wa = '$no_wa',
            status_verifikasi = '$status_verifikasi'
         WHERE user_id = $id"
    );


    // ======================================
    // HASIL UPDATE
    // ======================================

    if ($update) {

        header("Location: user.php");
        exit;

    } else {

        die("Gagal menyimpan data: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data User - AKSARA KOST</title>
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

        .custom-input-group .form-control::placeholder {
            color: #94a3b8;
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
                <i class="bi bi-pencil-fill fs-5"></i> Edit Data User
            </h1>
            <p class="header-subtitle">Perbarui informasi detail akun pengguna di bawah ini.</p>
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
                    <h2 class="left-info-title">Data User</h2>
                    <p class="left-info-desc">Perbarui data informasi akun pengguna sistem dengan benar.</p>
                    <div class="divider-home">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Input Form -->
            <div class="col-md-8 ps-md-4">
                <form method="POST">
                    
                    <!-- NAMA -->
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <input
                                type="text"
                                name="nama"
                                class="form-control"
                                value="<?= htmlspecialchars($user['nama']); ?>"
                                placeholder="Masukkan nama lengkap"
                                required
                            >
                        </div>
                    </div>

                    <!-- EMAIL -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-envelope-fill"></i>
                            </div>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?= htmlspecialchars($user['email']); ?>"
                                placeholder="Masukkan alamat email"
                                required
                            >
                        </div>
                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                            <input
                                type="text"
                                name="password"
                                class="form-control"
                                value="<?= htmlspecialchars($user['password']); ?>"
                                placeholder="Masukkan password"
                                required
                            >
                        </div>
                    </div>

                    <!-- ROLE -->
                    <div class="mb-3">
                        <label class="form-label">Role / Hak Akses</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <select name="role" class="form-select" required>
                                <option value="Admin" <?= ($user['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="Owner" <?= ($user['role'] == 'Owner') ? 'selected' : ''; ?>>Owner</option>
                                <option value="User" <?= ($user['role'] == 'User') ? 'selected' : ''; ?>>User</option>
                            </select>
                        </div>
                    </div>

                    <!-- NO WA -->
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-whatsapp"></i>
                            </div>
                            <input
                                type="text"
                                name="no_wa"
                                class="form-control"
                                value="<?= htmlspecialchars($user['no_wa']); ?>"
                                placeholder="Contoh: 081234567890"
                            >
                        </div>
                    </div>

                    <!-- STATUS VERIFIKASI -->
                    <div class="mb-4">
                        <label class="form-label">Status Verifikasi</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <select name="status_verifikasi" class="form-select" required>
                                <option value="Menunggu" <?= ($user['status_verifikasi'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="Terverifikasi" <?= ($user['status_verifikasi'] == 'Terverifikasi') ? 'selected' : ''; ?>>Terverifikasi</option>
                            </select>
                        </div>
                    </div>

                    <!-- TOMBOL AKSI -->
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="user.php" class="btn-kembali">
                            <i class="bi bi-arrow-counterclockwise"></i> Kembali
                        </a>
                        <button
                            type="submit"
                            name="simpan"
                            class="btn-simpan"
                        >
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