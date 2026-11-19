<?php
include '../database.php';

// ==========================================
// CEK ID KOS
// ==========================================

if (!isset($_GET['id'])) {
    die("ID kos tidak ditemukan.");
}

$id = intval($_GET['id']);


// ==========================================
// AMBIL DATA KOS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM kos WHERE kos_id = $id"
);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$kos = mysqli_fetch_assoc($query);

if (!$kos) {
    die("Data kos tidak ditemukan.");
}


// ==========================================
// PROSES SIMPAN UPDATE
// ==========================================

if (isset($_POST['simpan'])) {

    $owner_id          = intval($_POST['owner_id']);
    $nama_kos          = mysqli_real_escape_string($conn, $_POST['nama_kos']);
    $alamat            = mysqli_real_escape_string($conn, $_POST['alamat']);
    $latitude          = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude         = mysqli_real_escape_string($conn, $_POST['longitude']);
    $tipe_kos          = mysqli_real_escape_string($conn, $_POST['tipe_kos']);
    $status_verifikasi = mysqli_real_escape_string($conn, $_POST['status_verifikasi']);


    // ======================================
    // UPDATE DATA KOS
    // ======================================

    $update = mysqli_query(
        $conn,
        "UPDATE kos SET
            owner_id = '$owner_id',
            nama_kos = '$nama_kos',
            alamat = '$alamat',
            latitude = '$latitude',
            longitude = '$longitude',
            tipe_kos = '$tipe_kos',
            status_verifikasi = '$status_verifikasi'
         WHERE kos_id = $id"
    );


    // ======================================
    // HASIL UPDATE
    // ======================================

    if ($update) {

        header("Location: kos.php");
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Data Kos</title>
    <link rel="icon" href="../uploads/logoaksara.png">


    <!-- Bootstrap 5 -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            background-color: #f8f9fa;
        }

        .container-edit {
            width: 90%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .card-edit {
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }

        .judul {
            font-size: 28px;
            font-weight: 600;
            padding: 18px 22px;
            border-bottom: 1px solid #ddd;
        }

        .isi {
            padding: 25px;
        }

        .form-label {
            font-size: 17px;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            height: 48px;
            font-size: 16px;
        }

        textarea.form-control {
            height: auto;
        }

        .tombol {
            margin-top: 20px;
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


<div class="container-edit">

    <div class="card-edit">


        <!-- JUDUL -->

        <div class="judul">
            Edit Data Kos
        </div>


        <div class="isi">

            <form method="POST">


                <!-- OWNER ID -->

                <div class="mb-4">

                    <label class="form-label">
                        Owner ID
                    </label>

                    <input
                        type="number"
                        name="owner_id"
                        class="form-control"
                        value="<?= htmlspecialchars($kos['owner_id']); ?>"
                        required
                    >

                </div>


                <!-- NAMA KOS -->

                <div class="mb-4">

                    <label class="form-label">
                        Nama Kos
                    </label>

                    <input
                        type="text"
                        name="nama_kos"
                        class="form-control"
                        value="<?= htmlspecialchars($kos['nama_kos']); ?>"
                        required
                    >

                </div>


                <!-- ALAMAT -->

                <div class="mb-4">

                    <label class="form-label">
                        Alamat
                    </label>

                    <textarea
                        name="alamat"
                        class="form-control"
                        rows="3"
                        required
                    ><?= htmlspecialchars($kos['alamat']); ?></textarea>

                </div>


                <!-- LATITUDE -->

                <div class="mb-4">

                    <label class="form-label">
                        Latitude
                    </label>

                    <input
                        type="text"
                        name="latitude"
                        class="form-control"
                        value="<?= htmlspecialchars($kos['latitude']); ?>"
                    >

                </div>


                <!-- LONGITUDE -->

                <div class="mb-4">

                    <label class="form-label">
                        Longitude
                    </label>

                    <input
                        type="text"
                        name="longitude"
                        class="form-control"
                        value="<?= htmlspecialchars($kos['longitude']); ?>"
                    >

                </div>


                <!-- TIPE KOS -->

                <div class="mb-4">

                    <label class="form-label">
                        Tipe Kos
                    </label>

                    <select name="tipe_kos" class="form-select" required>
                        <option value="Putra" <?= ($kos['tipe_kos'] == 'Putra') ? 'selected' : ''; ?>>Putra</option>
                        <option value="Putri" <?= ($kos['tipe_kos'] == 'Putri') ? 'selected' : ''; ?>>Putri</option>
                        <option value="Campur" <?= ($kos['tipe_kos'] == 'Campur') ? 'selected' : ''; ?>>Campur</option>
                    </select>

                </div>


                <!-- STATUS VERIFIKASI -->

                <div class="mb-4">

                    <label class="form-label">
                        Status Verifikasi
                    </label>

                    <select name="status_verifikasi" class="form-select" required>
                        <option value="Menunggu" <?= ($kos['status_verifikasi'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Aktif" <?= ($kos['status_verifikasi'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Non-aktif" <?= ($kos['status_verifikasi'] == 'Non-aktif') ? 'selected' : ''; ?>>Non-aktif</option>
                    </select>

                </div>


                <!-- TOMBOL -->

                <div class="tombol">

                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-success px-4"
                    >
                        Simpan
                    </button>


                    <a
                        href="kos.php"
                        class="btn btn-secondary px-4"
                    >
                        Kembali
                    </a>

                </div>


            </form>

        </div>

    </div>

</div>


</body>

</html>