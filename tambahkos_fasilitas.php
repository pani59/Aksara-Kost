<?php
include 'database.php';

/* =====================================================
   AMBIL DATA KOST & FASILITAS UNTUK DROPDOWN
   ===================================================== */
$query_kos = mysqli_query($conn, "SELECT * FROM kos ORDER BY kos_id ASC");
$query_fasilitas = mysqli_query($conn, "SELECT * FROM fasilitas ORDER BY fasilitas_id ASC");

/* =====================================================
   PROSES SIMPAN KOST FASILITAS
   ===================================================== */
if (isset($_POST['simpan'])) {

    $kos_id       = intval($_POST['kos_id']);
    $fasilitas_id = intval($_POST['fasilitas_id']);

    /* =================================================
       SIMPAN KE DATABASE TABEL KOS_FASILITAS
       ================================================= */
    $query = "INSERT INTO kos_fasilitas (kos_id, fasilitas_id) VALUES ('$kos_id', '$fasilitas_id')";

    if (mysqli_query($conn, $query)) {
        header("Location: kos_fasilitas.php");
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
    <title>Tambah Data Kost Fasilitas</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            margin: 0;
            background-color: #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* HEADER */
        .navbar-custom {
            height: 62px;
            background-color: #2E7D5B;
            display: flex;
            align-items: center;
            padding: 0 28px;
        }
        .navbar-brand-custom {
            color: white;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 15px;
        }

        /* Kotak kartu putih di tengah */
        .card-custom {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }

        .judul {
            font-size: 26px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 5px;
        }

        .sub-judul {
            font-size: 14px;
            color: #666666;
            margin-bottom: 25px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }

        .form-control {
            font-size: 14px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px 15px;
        }

        select.form-control {
            height: 45px;
        }

        .form-control:focus {
            border-color: #1e7e34;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(30, 126, 52, 0.25);
        }

        /* Posisi tombol di sebelah kanan bawah */
        .tombol-container {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            font-size: 14px;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 5px;
        }

        .btn-tambah {
            background-color: #1e7e34;
            border-color: #1e7e34;
            color: white;
        }

        .btn-tambah:hover {
            background-color: #155724;
            color: white;
        }

        .btn-kembali {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .btn-kembali:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
<style id="tema-admin-owner-sage">

/* =====================================================
   TEMA KONSISTEN ADMIN & OWNER - MENGIKUTI USER.PHP
   Hanya CSS/tampilan; tidak mengubah PHP, SQL, atau database.
   ===================================================== */
:root{--sage:#81c784;--sage-dark:#137333;--sage-soft:#e6f4ea;--page:#f8faf9;--ink:#2c3e50;--muted:#64748b;--line:#edf2f0;}
html,body{font-family:'Source Sans Pro',Arial,sans-serif!important;color:var(--ink)!important;}
body{background:var(--page)!important;}
.content-wrapper{background:var(--page)!important;min-height:100vh!important;}
.content-header,.content{padding-left:20px;padding-right:20px;}
.judul-halaman,.content-header h1{color:var(--ink)!important;font-weight:600!important;}
.main-sidebar,.main-sidebar.sidebar-dark-primary,.sidebar-dark-primary{background:#fff!important;border-right:1px solid var(--line)!important;}
.main-sidebar{box-shadow:2px 0 12px rgba(0,0,0,.025)!important;}
.main-sidebar .brand-link{background:#fff!important;border-bottom:1px solid var(--line)!important;color:var(--ink)!important;}
.sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link,
.sidebar .nav-sidebar>.nav-item>.nav-link{color:#4a5568!important;border-radius:8px!important;margin:2px 0!important;}
.sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active,
.sidebar .nav-sidebar>.nav-item>.nav-link.active{background:var(--sage-soft)!important;color:var(--sage-dark)!important;font-weight:600!important;box-shadow:none!important;}
.sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover,
.sidebar .nav-sidebar>.nav-item>.nav-link:hover{background:#f1f8f3!important;color:var(--sage-dark)!important;}
.sidebar .user-panel{border-bottom:1px solid var(--line)!important;}
.sidebar .user-panel .info a{color:var(--ink)!important;font-weight:700!important;}
.sidebar .user-panel .image img{object-fit:cover;}
.main-header.navbar{background:var(--page)!important;border-bottom:1px solid var(--line)!important;}
.main-header .nav-link{color:#64748b!important;}
.admin-profile-badge{background:#fff!important;border:1px solid #dcdcdc!important;border-radius:40px!important;box-shadow:0 2px 6px rgba(0,0,0,.04)!important;}
.card,.card-custom{background:#fff!important;border:1px solid var(--line)!important;border-radius:12px!important;box-shadow:0 4px 12px rgba(0,0,0,.035)!important;}
.card-header{background:#fff!important;border-bottom:1px solid var(--line)!important;border-radius:12px 12px 0 0!important;}
.table{color:var(--ink)!important;}
.table thead th,.table thead.thead-light th{background:#f5f8f6!important;color:#405247!important;border-color:var(--line)!important;font-weight:600!important;}
.table td,.table th{vertical-align:middle!important;border-color:var(--line)!important;}
.table-hover tbody tr:hover{background:#f7fbf8!important;}
.btn-success,.btn-primary,.btn-info{background:var(--sage)!important;border-color:var(--sage)!important;color:#fff!important;}
.btn-success:hover,.btn-primary:hover,.btn-info:hover{background:#66bb6a!important;border-color:#66bb6a!important;}
.btn-outline-success{color:var(--sage-dark)!important;border-color:var(--sage)!important;}
.btn-outline-success:hover{background:var(--sage-soft)!important;}
.form-control,.custom-select{border:1px solid #dfe7e2!important;border-radius:7px!important;box-shadow:none!important;}
.form-control:focus,.custom-select:focus{border-color:var(--sage)!important;box-shadow:0 0 0 .15rem rgba(129,199,132,.18)!important;}
label{color:#405247;font-weight:600;}
.badge-success,.badge-info{background:var(--sage)!important;color:#fff!important;}
.page-link{color:var(--sage-dark)!important;}
.page-item.active .page-link{background:var(--sage)!important;border-color:var(--sage)!important;color:#fff!important;}
.content .btn,.card .btn{border-radius:7px!important;}

</style>

<style id="ukuran-profil-sidebar-navbar">
/* Ukuran foto dan nama pada sidebar */
.main-sidebar .user-panel {
    min-height: 88px !important;
    padding-top: 10px !important;
    padding-bottom: 18px !important;
    display: flex !important;
    align-items: center !important;
}
.main-sidebar .user-panel .image img {
    width: 62px !important;
    height: 62px !important;
    min-width: 62px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
}
.main-sidebar .user-panel .info {
    padding-left: 13px !important;
    min-width: 0 !important;
    white-space: normal !important;
}
.main-sidebar .user-panel .info a {
    display: block !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    line-height: 1.3 !important;
    white-space: normal !important;
}

/* Bingkai, foto, dan tulisan profil di kanan atas navbar */
.admin-profile-badge {
    padding: 9px 19px 9px 10px !important;
    min-height: 66px !important;
    border-radius: 40px !important;
    display: flex !important;
    align-items: center !important;
}
.admin-profile-badge img {
    width: 50px !important;
    height: 50px !important;
    min-width: 50px !important;
    object-fit: cover !important;
    border-radius: 50% !important;
}
.admin-profile-badge span {
    font-size: 18px !important;
    font-weight: 700 !important;
    margin-left: 10px !important;
}
@media (max-width: 575.98px) {
    .main-sidebar .user-panel .image img {
        width: 54px !important; height: 54px !important; min-width: 54px !important;
    }
    .main-sidebar .user-panel .info a { font-size: 14px !important; }
    .admin-profile-badge { padding: 6px 12px 6px 7px !important; min-height: 54px !important; }
    .admin-profile-badge img { width: 40px !important; height: 40px !important; min-width: 40px !important; }
    .admin-profile-badge span { font-size: 15px !important; }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar-custom">
    <div class="navbar-brand-custom">
        <i class="fas fa-home"></i>
        AKSARA KOST
    </div>
</nav>

<!-- CONTENT -->
<div class="content">
    <div class="card-custom">

        <!-- JUDUL & SUBJUDUL -->
        <div class="judul">
            Tambah Data Kost Fasilitas
        </div>
        <div class="sub-judul">
            Silahkan tambah data relasi fasilitas kost di bawah ini.
        </div>

        <!-- FORM TAMBAH KOST FASILITAS -->
        <form method="POST">

            <!-- PILIH KOST -->
            <div class="form-group">
                <label>Pilih Kost</label>
                <select name="kos_id" class="form-control" required>
                    <option value="">-- Pilih Kost --</option>
                    <?php while ($row_kos = mysqli_fetch_assoc($query_kos)): ?>
                        <option value="<?= $row_kos['kos_id']; ?>">
                            <?= isset($row_kos['nama_kos']) ? htmlspecialchars($row_kos['nama_kos']) : 'Kos ID: ' . $row_kos['kos_id']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- PILIH FASILITAS -->
            <div class="form-group">
                <label>Pilih Fasilitas</label>
                <select name="fasilitas_id" class="form-control" required>
                    <option value="">-- Pilih Fasilitas --</option>
                    <?php while ($row_fasilitas = mysqli_fetch_assoc($query_fasilitas)): ?>
                        <option value="<?= $row_fasilitas['fasilitas_id']; ?>">
                            <?= htmlspecialchars($row_fasilitas['nama_fasilitas']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- BUTTON (Posisinya di kanan bawah form) -->
            <div class="tombol-container">
                <a href="kos_fasilitas.php" class="btn btn-kembali">
                    Kembali
                </a>
                <button type="submit" name="simpan" class="btn btn-tambah">
                    Tambah
                </button>
            </div>

        </form>

    </div>
</div>

</body>
</html>