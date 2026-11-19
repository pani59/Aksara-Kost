<?php
session_start();
require_once '../database.php';

// =================================================
// AMBIL DATA REVIEW DARI DATABASE
// =================================================
$query = "SELECT * FROM review ORDER BY review_id ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query error: " . mysqli_error($conn));
}
// LOGIKA MENGECEK FILE FOTO FISIK UNIK BERDASARKAN USER_ID
$found_foto = 'default.jpeg';
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $extensions = ['jpg', 'jpeg', 'png', 'gif'];
    foreach ($extensions as $ext) {
        $filename = 'user_' . $uid . '.' . $ext;
        if (file_exists(__DIR__ . '/../uploads/' . $filename)) {
            $found_foto = $filename;
            break;
        }
    }
}
$_SESSION['foto'] = $found_foto;

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Review - Owner</title>

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

        /* PROFIL OWNER KANAN ATAS */
        .owner-profile-badge {
            background: #ffffff;
            border: 1px solid #dcdcdc;
            padding: 9px 19px 9px 10px;
            border-radius: 40px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
        }

        .owner-profile-badge img {
            width: 50px !important;
            height: 50px !important;
            object-fit: cover;
        }

        .owner-profile-badge span {
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

        /* CARD */
        .card {
            background-color: #ffffff !important;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #edf2f0;
        }

        /* TABLE */
        .table td,
        .table th {
            vertical-align: middle !important;
        }

        /* WARNA HEADER TABEL HIJAU & SEGAR */
        .table thead th {
            background-color: #e6f4ea !important;
            color: #137333 !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #ceead6 !important;
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* KOLOM AKSI */
        .kolom-aksi {
            width: 150px !important;
            min-width: 150px !important;
            text-align: center !important;
            white-space: nowrap !important;
        }

        .kolom-aksi .btn {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            margin: 2px !important;
            padding: 4px 14px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
        }

        /* BINTANG RATING */
        .bintang-kuning {
            color: #ffc107 !important;
            font-size: 15px;
        }

        /* TOMBOL DENGAN SUDUT MELENGKUNG */
        .btn-success,
        .btn-primary {
            background-color: #137333 !important;
            border-color: #137333 !important;
            color: #ffffff !important;
            border-radius: 20px !important;
            padding-left: 15px;
            padding-right: 15px;
        }

        .btn-success:hover,
        .btn-primary:hover {
            background-color: #0f5c28 !important;
            border-color: #0f5c28 !important;
        }

        .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            border-radius: 20px !important;
            padding-left: 15px;
            padding-right: 15px;
        }

        .btn {
            border-radius: 20px !important;
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
                <a href="index2.php" class="nav-link text-secondary font-weight-bold">Home</a>
            </li>
        </ul>
        <!-- PROFIL OWNER -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item d-flex align-items-center pr-2">
                <div class="owner-profile-badge">
    <a class="d-flex align-items-center text-decoration-none" href="#">
        <img src="../uploads/<?= htmlspecialchars($_SESSION['foto'] ?? 'default.jpeg'); ?>" class="img-circle elevation-1" alt="User" onerror="this.src='../uploads/default.jpeg';">
        <span class="text-secondary font-weight-bold ml-2">Owner</span>
    </a>
</div>
            </li>
        </ul>
    </nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link py-2" style="visibility:hidden;height:0;padding:0;margin:0;border:none;"></a>

        <div class="sidebar pt-2">
           <!-- USER PANEL -->
            <div class="user-panel mt-2 pb-3 mb-3 d-flex align-items-center">
    <div class="image">
        <img src="../uploads/<?= htmlspecialchars($_SESSION['foto'] ?? 'default.jpeg'); ?>" class="img-circle elevation-2" alt="User Image" onerror="this.src='../uploads/default.jpeg';">
    </div>
    <div class="info">
        <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['nama'] ?? 'Owner'); ?></a>
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
                        <a href="kosowner.php" class="nav-link">
                            <i class="nav-icon fas fa-home"></i>
                            <p>KOST</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="fotokos_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-images"></i>
                            <p>FOTO KOST</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kosfasilitas_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-layer-group"></i>
                            <p>KOST FASILITAS</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kamar_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-bed"></i>
                            <p>KAMAR</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="booking_owner.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>BOOKING</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="review_owner.php" class="nav-link active">
                            <i class="nav-icon fas fa-star"></i>
                            <p>REVIEW</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="laporan_owner.php" class="nav-link">
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
                <h1 class="judul-halaman">Data Review</h1>
            </div>
        </section>

        <section class="content mb-4">
            <div class="container-fluid">
                
                <!-- CARD TABLE -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Booking ID</th>
                                        <th>Rating</th>
                                        <th>Komentar</th>
                                        <th>Dibuat Pada</th>
                                        <th class="text-center kolom-aksi">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($result) > 0) {
                                    while ($data = mysqli_fetch_assoc($result)) {
                                        $rating = (int)$data['rating'];
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <!-- DIPERBAIKI: Menggunakan booking_id dan ditutup dengan benar -->
                                        <td>Booking #<?= htmlspecialchars($data['booking_id'] ?? ''); ?></td>
                                        <td>
                                            <span class="bintang-kuning">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo '<i class="fas fa-star"></i> ';
                                                    } else {
                                                        echo '<i class="far fa-star"></i> ';
                                                    }
                                                }
                                                ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">(<?= $rating; ?>/5)</small>
                                        </td>
                                        <!-- DIPERBAIKI: Menambahkan pengaman null ?? '' -->
                                        <td><?= htmlspecialchars($data['komentar'] ?? ''); ?></td>
                                        <td>
                                            <?= !empty($data['dibuat_pada']) ? date('d-m-Y H:i', strtotime($data['dibuat_pada'])) : '-'; ?>
                                        </td>
                                        <td class="kolom-aksi">
                                            <a href="hapusreview_owner.php?id=<?= (int)$data['review_id']; ?>" class="btn btn-danger btn-sm font-weight-bold" onclick="return confirm('Yakin ingin menghapus ulasan ini?');">
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada data ulasan.</td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        
        <!-- FOOTER -->
        <div class="text-center py-4 text-muted" style="font-size:14px;">
            <span>Panel Owner Kost &bull; Kelola Data dengan Mudah</span>
        </div>
    </div>

</div>

<!-- JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>