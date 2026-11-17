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

    $update = "UPDATE booking SET status_booking = '$status_booking' WHERE booking_id = '$booking_id'";
    if (mysqli_query($conn, $update)) {
        header("Location: booking_owner.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Booking - Owner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f6f9; padding: 50px 20px; font-family: Arial, sans-serif; }
        .card-custom { max-width: 650px; margin: auto; background: #fff; padding: 35px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-group label { font-weight: 500; font-size: 14px; color: #444; margin-bottom: 6px; }
        .form-control { border-radius: 6px; padding: 10px 12px; height: auto; }
        .form-control[readonly] { background-color: #f8f9fa; cursor: not-allowed; }
        .btn-simpan { background-color: #198754; color: #fff; font-weight: bold; padding: 8px 24px; border-radius: 6px; }
        .btn-simpan:hover { background-color: #157347; color: #fff; }
        .btn-kembali { background-color: #0d6efd; color: #fff; font-weight: bold; padding: 8px 24px; border-radius: 6px; }
        .btn-kembali:hover { background-color: #0b5ed7; color: #fff; }
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

<div class="card-custom">
    <h2 class="mb-4">Edit Data Booking (Owner)</h2>

    <form method="POST">
        <div class="form-group mb-3">
            <label>Nama Pemesan</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($booking['nama_user']); ?>" readonly>
        </div>

        <div class="form-group mb-3">
            <label>Kost & Kamar</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($booking['nama_kos']); ?> - Kamar <?= htmlspecialchars($booking['no_kamar']); ?>" readonly>
        </div>

        <div class="form-group mb-3">
            <label>Tanggal Survey</label>
            <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($booking['tanggal_survey'])); ?>" readonly>
        </div>

        <div class="form-group mb-4">
            <label>Status Booking</label>
            <select name="status_booking" class="form-control" required>
                <option value="Menunggu" <?= $booking['status_booking'] === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                <option value="Disetujui" <?= $booking['status_booking'] === 'Disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                <option value="Ditolak" <?= $booking['status_booking'] === 'Ditolak' ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>

        <div class="pt-2">
            <button type="submit" class="btn btn-simpan">Simpan</button>
            <a href="booking_owner.php" class="btn btn-kembali ml-2">Kembali</a>
        </div>
    </form>
</div>

</body>
</html>