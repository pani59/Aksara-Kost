<?php
session_start();
require_once 'database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID KAMAR
// ==========================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Kamar tidak ditemukan.");
}

$kamar_id = intval($_GET['id']);

// ==========================================
// AMBIL DATA KAMAR SAAT INI
// ==========================================
$query = mysqli_query($conn, "SELECT * FROM kamar WHERE kamar_id = $kamar_id");
if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$data_kamar = mysqli_fetch_assoc($query);

if (!$data_kamar) {
    die("Data kamar tidak ditemukan di database.");
}

// AMBIL LIST KOST UNTUK DROPDOWN
$query_kos = mysqli_query($conn, "SELECT * FROM kos ORDER BY kos_id ASC");

// ==========================================
// PROSES UPDATE KAMAR
// ==========================================
if (isset($_POST['simpan'])) {

    $kos_id       = intval($_POST['kos_id']);
    $no_kamar     = mysqli_real_escape_string($conn, trim($_POST['no_kamar']));
    $tipe_kamar   = mysqli_real_escape_string($conn, trim($_POST['tipe_kamar']));
    $harga        = intval($_POST['harga']);
    $status_kamar = mysqli_real_escape_string($conn, trim($_POST['status_kamar']));

    $update = "UPDATE kamar SET
                kos_id       = '$kos_id',
                no_kamar     = '$no_kamar',
                tipe_kamar   = '$tipe_kamar',
                harga        = '$harga',
                status_kamar = '$status_kamar'
               WHERE kamar_id = $kamar_id";

    if (mysqli_query($conn, $update)) {
        header("Location: kamar_owner.php");
        exit;
    } else {
        die("Gagal memperbarui data: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Kamar</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body {
            margin: 0;
            background-color: #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
        }

        .navbar-custom {
            height: 70px;
            background-color: #343a40;
            display: flex;
            align-items: center;
        }

        .navbar-brand-custom {
            color: white;
            font-size: 25px;
            margin-left: 40px;
            font-weight: 500;
        }

        .content {
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            padding: 55px 20px 50px 20px;
        }

        .judul {
            font-size: 42px;
            font-weight: 400;
            color: #343a40;
            margin-bottom: 20px;
        }

        .garis {
            border-top: 1px solid #dee2e6;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 18px;
            color: #343a40;
            margin-bottom: 8px;
        }

        .form-control {
            height: 52px;
            font-size: 17px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px 15px;
        }

        .tombol {
            margin-top: 30px;
        }

        .btn {
            font-size: 18px;
            padding: 11px 25px;
            border-radius: 5px;
        }

        .btn-simpan {
            background-color: #198754;
            border-color: #198754;
            color: white;
        }

        .btn-simpan:hover {
            background-color: #157347;
            color: white;
        }

        .btn-kembali {
            background-color: #5a6268;
            border-color: #5a6268;
            color: white;
        }

        .btn-kembali:hover {
            background-color: #4e555b;
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
        CRUD PHP
    </div>
</nav>

<!-- CONTENT -->
<div class="content">

    <!-- JUDUL -->
    <div class="judul">
        Edit Data Kamar
    </div>

    <div class="garis"></div>

    <!-- FORM EDIT KAMAR -->
    <form method="POST">

        <!-- PILIH KOST -->
        <div class="form-group">
            <label>Pilih Kost</label>
            <select name="kos_id" class="form-control" required>
                <?php while ($row_kos = mysqli_fetch_assoc($query_kos)): ?>
                    <option value="<?= $row_kos['kos_id']; ?>" <?= ($row_kos['kos_id'] == $data_kamar['kos_id']) ? 'selected' : ''; ?>>
                        <?= !empty($row_kos['nama_kos']) ? htmlspecialchars($row_kos['nama_kos']) : 'Kos ID: ' . $row_kos['kos_id']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- NO KAMAR -->
        <div class="form-group">
            <label>Nomor Kamar</label>
            <input type="text" name="no_kamar" class="form-control" value="<?= htmlspecialchars($data_kamar['no_kamar']); ?>" required>
        </div>

        <!-- TIPE KAMAR -->
        <div class="form-group">
            <label>Tipe Kamar</label>
            <input type="text" name="tipe_kamar" class="form-control" value="<?= htmlspecialchars($data_kamar['tipe_kamar']); ?>" required>
        </div>

        <!-- HARGA -->
        <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="harga" class="form-control" value="<?= $data_kamar['harga']; ?>" required>
        </div>

        <!-- STATUS KAMAR -->
        <div class="form-group">
            <label>Status Kamar</label>
            <select name="status_kamar" class="form-control" required>
                <option value="Kosong" <?= ($data_kamar['status_kamar'] == 'Kosong') ? 'selected' : ''; ?>>Kosong</option>
                <option value="Terisi" <?= ($data_kamar['status_kamar'] == 'Terisi') ? 'selected' : ''; ?>>Terisi</option>
            </select>
        </div>

        <!-- BUTTON -->
        <div class="tombol">
            <button type="submit" name="simpan" class="btn btn-simpan">
                Simpan
            </button>

            <a href="kamar_owner.php" class="btn btn-kembali ml-2">
                Kembali
            </a>
        </div>

    </form>

</div>

</body>
</html>