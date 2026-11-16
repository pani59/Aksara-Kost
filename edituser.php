<?php
include 'database.php';

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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Data User</title>


    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons untuk ikon pada tombol -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >


    <style>

        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-edit {
            width: 90%;
            max-width: 850px;
            margin: 50px auto;
        }

        .card-edit {
            background: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .form-title {
            font-size: 26px;
            font-weight: 700;
            color: #1e7e34;
            margin-bottom: 5px;
        }

        .form-subtitle {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 30px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            height: 48px;
            font-size: 14px;
            border-color: #ced4da;
            border-radius: 8px;
            padding-left: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #1e7e34;
            box-shadow: 0 0 0 0.2rem rgba(30, 126, 52, 0.15);
        }

        /* Desain Tombol Modern */
        .tombol-container {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 35px;
        }

        .btn {
            font-size: 14px;
            font-weight: 600;
            padding: 10px 22px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-tambah {
            background-color: #1e7e34;
            border-color: #1e7e34;
            color: white;
        }

        .btn-tambah:hover {
            background-color: #155724;
            border-color: #155724;
            color: white;
            transform: translateY(-1px);
        }

        .btn-kembali {
            background-color: #e9ecef;
            border-color: #e9ecef;
            color: #495057;
        }

        .btn-kembali:hover {
            background-color: #dde2e6;
            border-color: #dde2e6;
            color: #212529;
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

        <div class="form-title">
            Edit Data User
        </div>
        <div class="form-subtitle">
            Perbarui informasi detail akun pengguna di bawah ini.
        </div>


        <form method="POST">


            <!-- NAMA -->

            <div class="mb-3">

                <label class="form-label">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="nama"
                    class="form-control"
                    value="<?= htmlspecialchars($user['nama']); ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="mb-3">

                <label class="form-label">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($user['email']); ?>"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="mb-3">

                <label class="form-label">
                    Password
                </label>

                <input
                    type="text"
                    name="password"
                    class="form-control"
                    value="<?= htmlspecialchars($user['password']); ?>"
                    required
                >

            </div>


            <!-- ROLE -->

            <div class="mb-3">

                <label class="form-label">
                    Role / Hak Akses
                </label>

                <select name="role" class="form-select" required>
                    <option value="Admin" <?= ($user['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="Owner" <?= ($user['role'] == 'Owner') ? 'selected' : ''; ?>>Owner</option>
                    <option value="User" <?= ($user['role'] == 'User') ? 'selected' : ''; ?>>User</option>
                </select>

            </div>


            <!-- NO WA -->

            <div class="mb-3">

                <label class="form-label">
                    Nomor WhatsApp
                </label>

                <input
                    type="text"
                    name="no_wa"
                    class="form-control"
                    value="<?= htmlspecialchars($user['no_wa']); ?>"
                >

            </div>


            <!-- STATUS VERIFIKASI -->

            <div class="mb-3">

                <label class="form-label">
                    Status Verifikasi
                </label>

                <select name="status_verifikasi" class="form-select" required>
                    <option value="Menunggu" <?= ($user['status_verifikasi'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Terverifikasi" <?= ($user['status_verifikasi'] == 'Terverifikasi') ? 'selected' : ''; ?>>Terverifikasi</option>
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


</body>

</html>