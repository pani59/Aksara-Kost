<?php
include '../database.php';

/* =====================================================
   PROSES SIMPAN FASILITAS
   ===================================================== */

if (isset($_POST['simpan'])) {

    $nama_fasilitas = mysqli_real_escape_string($conn, $_POST['nama_fasilitas']);

    /* =================================================
       SIMPAN KE DATABASE TABEL FASILITAS
       ================================================= */

    $query = "INSERT INTO fasilitas (nama_fasilitas) VALUES ('$nama_fasilitas')";

    if (mysqli_query($conn, $query)) {

        header("Location: fasilitas.php");
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
    <title>Tambah Data Fasilitas</title>
    <link rel="icon" href="../assets/img/logoaksara.png">

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- FontAwesome untuk Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            margin: 0;
            background-color: #F2F4F3;
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #1F2937;
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

        /* CONTENT & CARD */
        .content {
            max-width: 680px;
            margin: 30px auto;
            padding: 0 20px 40px 20px;
        }

        .card-custom {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.07);
            padding: 32px 36px;
        }

        .judul {
            font-size: 26px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .subjudul {
            font-size: 14px;
            color: #6B7280;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 8px;
        }

        .form-control {
            height: 45px;
            font-size: 14px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            padding: 10px 14px;
        }

        .form-control:focus {
            border-color: #2E7D5B;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(46,125,91,.15);
        }

        /* TOMBOL */
        .tombol-container {
            margin-top: 26px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            font-size: 14px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 8px;
            border: none;
        }

        .btn-tambah {
            background-color: #2E7D5B;
            color: white;
        }

        .btn-tambah:hover {
            background-color: #256B4D;
            color: white;
        }

        .btn-kembali {
            background-color: #6B7280;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-kembali:hover {
            background-color: #575E69;
            color: white;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .card-custom { padding: 24px 20px; }
            .tombol-container { justify-content: stretch; }
            .tombol-container .btn { flex: 1; justify-content: center; }
            .navbar-custom { height: 56px; padding: 0 18px; }
            .navbar-brand-custom { font-size: 21px; }
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
            Tambah Data Fasilitas
        </div>
        <div class="subjudul">
            Silahkan tambah data fasilitas di bawah ini.
        </div>

        <!-- FORM TAMBAH FASILITAS -->
        <form method="POST">

            <!-- NAMA FASILITAS -->
            <div class="form-group">
                <label>Nama Fasilitas</label>
                <input type="text" name="nama_fasilitas" class="form-control" placeholder="Masukkan nama fasilitas" required>
            </div>

            <!-- BUTTON -->
            <div class="tombol-container">
                <a href="fasilitas.php" class="btn btn-kembali">
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