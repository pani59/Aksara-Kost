<?php
include '../database.php';

/* =====================================================
   PROSES SIMPAN FOTO KOS
   ===================================================== */

if (isset($_POST['simpan'])) {

    $kos_id = mysqli_real_escape_string($conn, $_POST['kos_id']);

    // Handling Upload File Foto
    $nama_file   = $_FILES['foto']['name'];
    $tmp_file    = $_FILES['foto']['tmp_name'];
    $folder_tujuan = '../uploads/';

    // Buat folder uploads jika belum ada
    if (!file_exists($folder_tujuan)) {
        mkdir($folder_tujuan, 0777, true);
    }

    if (!empty($nama_file)) {
        // Generate nama unik untuk menghindari bentrok nama file
        $ekstensi   = pathinfo($nama_file, PATHINFO_EXTENSION);
        $path_foto  = time() . '_' . rand(100, 999) . '.' . $ekstensi;

        move_uploaded_file($tmp_file, $folder_tujuan . $path_foto);
    } else {
        $path_foto = '';
    }

    /* =================================================
       SIMPAN KE DATABASE TABEL FOTO_KOS
       ================================================= */

    $query = "INSERT INTO foto_kos (kos_id, path_foto) VALUES ('$kos_id', '$path_foto')";

    if (mysqli_query($conn, $query)) {

        header("Location: fotokos_owner.php");
        exit;

    } else {

        echo "Gagal menyimpan data: " . mysqli_error($conn);
    }
}

// Ambil daftar Kos untuk opsi dropdown
$query_kos = "SELECT * FROM kos ORDER BY kos_id ASC";
$result_kos = mysqli_query($conn, $query_kos);
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto Kos - AKSARA KOST</title>
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

        .custom-input-group .form-select,
        .custom-input-group .form-control {
            border: none;
            background: transparent;
            box-shadow: none;
            padding-left: 14px;
            font-size: 0.95rem;
            color: #334155;
            width: 100%;
        }

        /* DRAG & DROP UPLOAD BOX */
        .upload-box {
            border: 2px dashed #9CC3AD;
            background-color: #EAF3ED;
            border-radius: 10px;
            padding: 22px 16px;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
        }
        .upload-box:hover {
            background-color: #DCEBE2;
            border-color: #2E7D5B;
        }
        .upload-box p {
            margin: 0;
            font-size: 14px;
            color: #2E7D5B;
            font-weight: 500;
        }
        .upload-box small {
            color: #6B7280;
            font-size: 12px;
        }
        #foto-input { display: none; }

        /* PREVIEW */
        .preview-wrap { display: none; margin-top: 14px; }
        .preview-wrap img {
            width: 160px;
            height: 110px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #D1D5DB;
        }
        .preview-label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1F2937;
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
                <i class="bi bi-pencil-fill fs-5"></i> Tambah Foto Kos
            </h1>
            <p class="header-subtitle">Silahkan tambah data foto kos di bawah ini.</p>
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
                    <h2 class="left-info-title">Foto Kos</h2>
                    <p class="left-info-desc">Unggah galeri foto properti kost dengan benar.</p>
                    <div class="divider-home">
                        <i class="bi bi-house-door-fill"></i>
                    </div>
                </div>
            </div>

            <!-- Sisi Kanan: Input Form -->
            <div class="col-md-8 ps-md-4">
                <form method="POST" enctype="multipart/form-data">
                    
                    <!-- KOS ID -->
                    <div class="mb-3">
                        <label class="form-label">Pilih Kos (Kos ID)</label>
                        <div class="custom-input-group">
                            <div class="input-icon-box">
                                <i class="bi bi-building-fill"></i>
                            </div>
                            <select name="kos_id" class="form-select" required>
                                <option value="">-- Pilih Kos --</option>
                                <?php
                                if ($result_kos && mysqli_num_rows($result_kos) > 0) {
                                    while ($row_kos = mysqli_fetch_assoc($result_kos)) {
                                        $nama = isset($row_kos['nama_kos']) ? $row_kos['nama_kos'] : 'Kos ID: ' . $row_kos['kos_id'];
                                        echo "<option value='" . $row_kos['kos_id'] . "'>" . htmlspecialchars($nama) . " (ID: " . $row_kos['kos_id'] . ")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- UPLOAD FOTO (PATH_FOTO) -->
                    <div class="mb-4">
                        <label class="form-label">Upload File Foto Kos</label>

                        <div class="upload-box" onclick="document.getElementById('foto-input').click()">
                            <p><i class="bi bi-cloud-arrow-up-fill fs-4 d-block mb-1"></i> Seret foto ke sini atau klik untuk upload.</p>
                            <small class="text-muted">Pilih berkas gambar (.jpg, .jpeg, .png, .webp). Maks. 5MB.</small>
                        </div>

                        <input type="file" name="foto" id="foto-input" accept="image/*" required>

                        <div class="preview-wrap" id="preview-wrap">
                            <div class="preview-label mt-3">Preview Foto:</div>
                            <img id="preview-img" src="" alt="Preview Foto">
                        </div>
                    </div>

                    <!-- TOMBOL AKSI -->
                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="fotokos_owner.php" class="btn-kembali">
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

<script>
// Script Preview foto + drag & drop
const input = document.getElementById('foto-input');
const box = document.querySelector('.upload-box');
const wrap = document.getElementById('preview-wrap');
const img = document.getElementById('preview-img');

input.addEventListener('change', function(){
    if(this.files && this.files[0]){
        const reader = new FileReader();
        reader.onload = function(e){
            img.src = e.target.result;
            wrap.style.display = 'block';
        }
        reader.readAsDataURL(this.files[0]);
    }
});

['dragover','dragleave','drop'].forEach(ev => {
    box.addEventListener(ev, e => {
        e.preventDefault();
        if(ev === 'drop' && e.dataTransfer.files.length > 0){
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
});
</script>

</body>
</html>