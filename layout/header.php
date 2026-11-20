<?php
session_start();
require_once '../database.php';

$nama_bulan_indo = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$tahun_ini = date('Y');
$bulan_ini = date('m');
$bulan_lalu = date('m', strtotime('-1 month'));
$tahun_bulan_lalu = date('Y', strtotime('-1 month'));

// 1. TOTAL PENDAPATAN & BOOKING DISETUJUI
$q_total = mysqli_query($conn, "SELECT SUM(k.harga) AS total_pendapatan, COUNT(b.booking_id) AS total_booking 
                              FROM booking b
                              JOIN kamar k ON b.kamar_id = k.kamar_id
                              WHERE b.status_booking IN ('Disetujui', 'Terisi')");
$data_total = mysqli_fetch_assoc($q_total);
$total_pendapatan = $data_total['total_pendapatan'] ?? 0;
$booking_disetujui = $data_total['total_booking'] ?? 0;

// 2. OKUPANSI KAMAR
// 2. OKUPANSI KAMAR (Dihitung langsung dari kamar yang statusnya 'Terisi' atau memiliki booking aktif)
$q_kamar_total = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kamar");
$total_kamar = mysqli_fetch_assoc($q_kamar_total)['total'] ?? 0;

$q_kamar_terisi = mysqli_query($conn, "SELECT COUNT(DISTINCT k.kamar_id) AS terisi 
                                       FROM kamar k
                                       JOIN booking b ON k.kamar_id = b.kamar_id
                                       WHERE k.status_kamar = 'Terisi' OR b.status_booking IN ('Disetujui', 'Terisi')");
$kamar_terisi = mysqli_fetch_assoc($q_kamar_terisi)['terisi'] ?? 0;

if ($kamar_terisi == 0) {
    $q_fallback = mysqli_query($conn, "SELECT COUNT(DISTINCT kamar_id) AS terisi FROM booking WHERE status_booking IN ('Disetujui', 'Terisi')");
    $kamar_terisi = mysqli_fetch_assoc($q_fallback)['terisi'] ?? 0;
}

// 3. PENDAPATAN BULAN INI VS BULAN LALU
$q_bln_ini = mysqli_query($conn, "SELECT SUM(k.harga) AS total 
                                  FROM booking b 
                                  JOIN kamar k ON b.kamar_id = k.kamar_id 
                                  WHERE b.status_booking IN ('Disetujui', 'Terisi') 
                                  AND MONTH(b.dibuat_pada) = '$bulan_ini' 
                                  AND YEAR(b.dibuat_pada) = '$tahun_ini'");
$tot_ini = mysqli_fetch_assoc($q_bln_ini)['total'] ?? 0;

$q_bln_lalu = mysqli_query($conn, "SELECT SUM(k.harga) AS total 
                                   FROM booking b 
                                   JOIN kamar k ON b.kamar_id = k.kamar_id 
                                   WHERE b.status_booking IN ('Disetujui', 'Terisi') 
                                   AND MONTH(b.dibuat_pada) = '$bulan_lalu' 
                                   AND YEAR(b.dibuat_pada) = '$tahun_bulan_lalu'");
$tot_lalu = mysqli_fetch_assoc($q_bln_lalu)['total'] ?? 0;

if ($tot_lalu > 0) {
    $persen_perubahan = (($tot_ini - $tot_lalu) / $tot_lalu) * 100;
} else {
    $persen_perubahan = $tot_ini > 0 ? 100 : 0;
}

// 4. DATA PENDAPATAN BULANAN
$nama_bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$pendapatan_bulanan = array_fill(1, 12, 0);

$q_bulanan = mysqli_query($conn, "SELECT MONTH(b.dibuat_pada) AS bulan, SUM(k.harga) AS total 
                                  FROM booking b 
                                  JOIN kamar k ON b.kamar_id = k.kamar_id 
                                  WHERE b.status_booking IN ('Disetujui', 'Terisi') 
                                  AND YEAR(b.dibuat_pada) = '$tahun_ini' 
                                  GROUP BY MONTH(b.dibuat_pada)");

while ($r = mysqli_fetch_assoc($q_bulanan)) {
    $pendapatan_bulanan[(int)$r['bulan']] = (float)$r['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="icon" href="../uploads/logoaksara.png">

    <!-- FONT -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- ADMINLTE & BOOTSTRAP -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #f8faf9 !important;
            font-family: 'Source Sans Pro', sans-serif;
            color: #2c3e50;
        }

        .content-wrapper {
            padding: 0;
            min-height: 100vh !important;
            background-color: #f8faf9 !important;
        }

        .content-header,
        .content {
            padding-left: 20px;
            padding-right: 20px;
        }

        .judul-halaman {
            font-size: 30px;
            font-weight: 600;
            margin-bottom: 2px;
            color: #2c3e50;
        }

        .sub-judul {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 20px;
        }

        /* SIDEBAR */
        .main-sidebar,
        .sidebar-dark-primary {
            background-color: #ffffff !important;
            border-right: 1px solid #edf2f0;
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link {
            color: #4a5568 !important;
            border-radius: 8px;
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: #e6f4ea !important;
            color: #137333 !important;
            font-weight: 600;
        }

        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: #f1f8f3 !important;
            color: #137333 !important;
        }

        .user-panel {
            border-bottom: 1px solid #edf2f0 !important;
            padding-bottom: 18px !important;
        }

        .user-panel .image img {
            width: 62px !important;
            height: 62px !important;
            object-fit: cover;
        }

        .user-panel .info {
            padding-left: 13px !important;
        }

        .user-panel .info a {
            color: #2c3e50 !important;
            font-size: 16px !important;
            font-weight: 700 !important;
        }

        /* NAVBAR */
        .main-header.navbar {
            background-color: #f8faf9 !important;
            border-bottom: none !important;
        }

        /* PROFIL ADMIN KANAN ATAS */
        .admin-profile-badge {
            background: #ffffff;
            border: 1px solid #dcdcdc;
            padding: 9px 19px 9px 10px;
            border-radius: 40px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
        }

        .admin-profile-badge img {
            width: 50px !important;
            height: 50px !important;
            object-fit: cover;
        }

        .admin-profile-badge span {
            font-size: 18px !important;
            font-weight: 700 !important;
        }

        /* SEARCH SIDEBAR */
        .sidebar .form-inline {
            width: 100%;
            padding-left: 8px;
            padding-right: 8px;
        }

        .sidebar .input-group[data-widget="sidebar-search"] {
            display: flex !important;
            width: 100% !important;
            background: #f1f3f4 !important;
            border: 1px solid #dce5df !important;
            border-radius: 9px !important;
            overflow: hidden;
        }

        .sidebar .form-control-sidebar {
            background: #f1f3f4 !important;
            color: #2c3e50 !important;
            -webkit-text-fill-color: #2c3e50 !important;
            caret-color: #137333 !important;
            border: 0 !important;
            font-size: 14px !important;
        }

        .sidebar .form-control-sidebar::placeholder {
            color: #7b8794 !important;
            opacity: 1 !important;
        }

        .sidebar .input-group-append .btn-sidebar {
            background: #e6f4ea !important;
            color: #137333 !important;
            border: 0 !important;
        }

        .sidebar .input-group-append .fa-search {
            color: #137333 !important;
            visibility: visible !important;
        }

        /* CARD & INFO BOX */
        .info-box {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #edf2f0;
            background: #fff;
        }

        .card {
            background-color: #ffffff !important;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #edf2f0;
        }

        .card-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid #edf2f0;
            border-top-left-radius: 12px !important;
            border-top-right-radius: 12px !important;
        }

        @media (max-width: 575px) {
            .judul-halaman {
                font-size: 24px;
            }

            .content-header,
            .content {
                padding-left: 12px;
                padding-right: 12px;
            }

            .user-panel .image img {
                width: 54px !important;
                height: 54px !important;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- NAVBAR -->
    <nav class="main-header navbar navbar-expand navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars text-secondary"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="index.php" class="nav-link text-secondary font-weight-bold">Home</a>
            </li>
        </ul>
        <!-- PROFIL ADMIN -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item d-flex align-items-center pr-2">
                <div class="admin-profile-badge">
                    <a class="d-flex align-items-center text-decoration-none" href="#">
                        <img src="../uploads/vani.jpeg" class="img-circle elevation-1" alt="User">
                        <span class="text-secondary font-weight-bold ml-2">Admin</span>
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link py-2" style="visibility:hidden;height:0;padding:0;margin:0;border:none;"></a>

        <div class="sidebar pt-2">
            <!-- USER PANEL -->
            <div class="user-panel mt-2 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <img src="../uploads/vani.jpeg" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">VANI PERMATASARI</a>
                </div>
            </div>

            <!-- SEARCH -->
            <div class="form-inline mb-2">
                <div class="input-group" data-widget="sidebar-search">
                    <input class="form-control form-control-sidebar" type="search" placeholder="Search..." aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-sidebar border-0">
                            <i class="fas fa-search fa-fw"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- MENU SIDEBAR -->
            <nav class="mt-2 px-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="user.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>USER</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kos.php" class="nav-link">
                            <i class="nav-icon fas fa-home"></i>
                            <p>KOST</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="foto_kos.php" class="nav-link">
                            <i class="nav-icon fas fa-images"></i>
                            <p>FOTO KOST</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="fasilitas.php" class="nav-link">
                            <i class="nav-icon fas fa-list-ul"></i>
                            <p>FASILITAS</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kos_fasilitas.php" class="nav-link">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>KOST FASILITAS</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kamar.php" class="nav-link">
                            <i class="nav-icon fas fa-bed"></i>
                            <p>KAMAR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="booking.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>BOOKING</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="review.php" class="nav-link">
                            <i class="nav-icon fas fa-star"></i>
                            <p>REVIEW</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="laporan.php" class="nav-link">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>LAPORAN</p>
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a href="logout.php" class="nav-link text-danger">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>LOGOUT</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- CONTENT WRAPPER -->
    <div class="content-wrapper">
        <section class="content-header pt-3">
            <div class="container-fluid">
                <h1 class="judul-halaman">Dashboard Admin</h1>
                <p class="sub-judul">Selamat datang di panel admin</p>
            </div>
        </section>

        <section class="content mb-4">
            <div class="container-fluid">
                
                <!-- 1. KARTU RINGKASAN INFORMASI (INFO-BOXES) -->
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-success elevation-1 bg-light"><i class="fas fa-wallet"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Total Pendapatan</span>
                                <span class="info-box-number font-weight-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-success elevation-1 bg-light"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Booking Disetujui</span>
                                <span class="info-box-number font-weight-bold"><?= $booking_disetujui; ?> Disetujui</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-success elevation-1 bg-light"><i class="fas fa-bed"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Kamar Terisi</span>
                                <span class="info-box-number font-weight-bold"><?= $kamar_terisi; ?> / <?= $total_kamar; ?> Terisi</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon text-success elevation-1 bg-light">
                                <i class="fas <?= $persen_perubahan >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text text-muted">Bulan Ini VS Lalu</span>
                                <span class="info-box-number font-weight-bold"><?= ($persen_perubahan >= 0 ? '+' : '') . number_format($persen_perubahan, 1); ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. GRAFIK CHART.JS -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold text-dark" style="font-size: 16px;"><i class="fas fa-chart-line mr-2 text-success"></i> Tren Pendapatan Bulanan</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold text-dark" style="font-size: 16px;"><i class="fas fa-chart-bar mr-2 text-success"></i> Realisasi Pendapatan</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        
        <!-- FOOTER -->
        <div class="text-center py-4 text-muted" style="font-size:14px;">
            <span>Panel Admin Kost &bull; Kelola Data dengan Mudah</span>
        </div>
    </div>

</div>

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const labels = <?= json_encode(array_values($nama_bulan)); ?>;
    const dataPendapatan = <?= json_encode(array_values($pendapatan_bulanan)); ?>;

    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataPendapatan,
                borderColor: '#81c784',
                backgroundColor: 'rgba(129, 199, 132, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataPendapatan,
                backgroundColor: '#81c784',
                borderRadius: 4
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
</script>

</body>
</html>