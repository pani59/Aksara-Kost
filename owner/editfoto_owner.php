<?php
include '../database.php';

// ==========================================
// CEK ID FOTO KOS
// ==========================================

if (!isset($_GET['id'])) {
    die("ID foto kos tidak ditemukan.");
}

$id = intval($_GET['id']);


// ==========================================
// AMBIL DATA FOTO KOS
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM foto_kos WHERE foto_id = $id"
);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$foto = mysqli_fetch_assoc($query);

if (!$foto) {
    die("Data foto kos tidak ditemukan.");
}

// Ambil daftar Kos untuk opsi dropdown
$query_kos = "SELECT * FROM kos ORDER BY kos_id ASC";
$result_kos = mysqli_query($conn, $query_kos);


// ==========================================
// PROSES SIMPAN UPDATE
// ==========================================

if (isset($_POST['simpan'])) {

    $kos_id    = mysqli_real_escape_string($conn, $_POST['kos_id']);
    $path_foto = $foto['path_foto']; // Default menggunakan file foto lama

    // Cek jika ada file foto baru yang diupload
    if (isset($_FILES['foto']['name']) && $_FILES['foto']['name'] != '') {
        $nama_file     = $_FILES['foto']['name'];
        $tmp_file      = $_FILES['foto']['tmp_name'];
        $folder_tujuan = '../uploads/';

        // Buat folder uploads jika belum ada
        if (!file_exists($folder_tujuan)) {
            mkdir($folder_tujuan, 0777, true);
        }

        // Hapus file foto lama jika berkasnya ada
        if (!empty($foto['path_foto']) && file_exists($folder_tujuan . $foto['path_foto'])) {
            unlink($folder_tujuan . $foto['path_foto']);
        }

        // Generate nama file baru secara unik
        $ekstensi  = pathinfo($nama_file, PATHINFO_EXTENSION);
        $path_foto = time() . '_' . rand(100, 999) . '.' . $ekstensi;

        move_uploaded_file($tmp_file, $folder_tujuan . $path_foto);
    }


    // ======================================
    // UPDATE DATA FOTO KOS
    // ======================================

    $update = mysqli_query(
        $conn,
        "UPDATE foto_kos SET
            kos_id = '$kos_id',
            path_foto = '$path_foto'
         WHERE foto_id = $id"
    );


    // ======================================
    // HASIL UPDATE
    // ======================================

    if ($update) {

        header("Location: fotokos_owner.php");
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

    <title>Edit Data Foto Kos</title>
    <link rel="icon" href="../assets/img/logoaksara.png">


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

        .img-preview-box {
            max-width: 200px;
            max-height: 150px;
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 5px;
            object-fit: cover;
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
            Edit Data Foto Kos
        </div>


        <div class="isi">

            <form method="POST" enctype="multipart/form-data">


                <!-- PILIH KOS ID -->

                <div class="mb-4">

                    <label class="form-label">
                        Pilih Kos (Kos ID)
                    </label>

                    <select name="kos_id" class="form-select" required>
                        <option value="">-- Pilih Kos --</option>
                        <?php
                        if ($result_kos && mysqli_num_rows($result_kos) > 0) {
                            while ($row_kos = mysqli_fetch_assoc($result_kos)) {
                                $selected = ($row_kos['kos_id'] == $foto['kos_id']) ? 'selected' : '';
                                $nama = isset($row_kos['nama_kos']) ? $row_kos['nama_kos'] : 'Kos ID: ' . $row_kos['kos_id'];
                                echo "<option value='" . $row_kos['kos_id'] . "' $selected>" . htmlspecialchars($nama) . " (ID: " . $row_kos['kos_id'] . ")</option>";
                            }
                        }
                        ?>
                    </select>

                </div>


                <!-- FOTO SAAT INI -->

                <div class="mb-4">

                    <label class="form-label d-block">
                        Foto Saat Ini
                    </label>

                    <?php if (!empty($foto['path_foto'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($foto['path_foto']); ?>" 
                             alt="Foto Kos Saat Ini" 
                             class="img-preview-box mb-2"
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/200x150?text=File+Tidak+Ada';">
                        <br>
                        <small class="text-muted">Nama File: <?= htmlspecialchars($foto['path_foto']); ?></small>
                    <?php else: ?>
                        <p class="text-muted">Belum ada foto terpilih.</p>
                    <?php endif; ?>

                </div>


                <!-- UPLOAD FOTO BARU -->

                <div class="mb-4">

                    <label class="form-label">
                        Ganti Foto Kos (Opsional)
                    </label>

                    <input
                        type="file"
                        name="foto"
                        class="form-control"
                        accept="image/*"
                    >

                    <small class="text-muted mt-1 d-block">
                        Kosongkan jika tidak ingin mengganti foto.
                    </small>

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
                        href="fotokos_owner.php"
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