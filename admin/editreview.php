<?php
include '../database.php';

// ==========================================
// CEK ID REVIEW
// ==========================================

if (!isset($_GET['id'])) {
    die("ID review tidak ditemukan.");
}

$id = intval($_GET['id']);


// ==========================================
// AMBIL DATA REVIEW
// ==========================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM review WHERE review_id = $id"
);

if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$review = mysqli_fetch_assoc($query);

if (!$review) {
    die("Data review tidak ditemukan.");
}

// AMBIL LIST BOOKING UNTUK DROPDOWN
$query_booking = mysqli_query($conn, "SELECT * FROM booking ORDER BY booking_id ASC");


// ==========================================
// PROSES SIMPAN UPDATE
// ==========================================

if (isset($_POST['simpan'])) {

    $booking_id = intval($_POST['booking_id']);
    $rating     = intval($_POST['rating']);
    $komentar   = mysqli_real_escape_string($conn, $_POST['komentar']);


    // ======================================
    // UPDATE DATA REVIEW
    // ======================================

    $update = mysqli_query(
        $conn,
        "UPDATE review SET
            booking_id = '$booking_id',
            rating = '$rating',
            komentar = '$komentar'
         WHERE review_id = $id"
    );


    // ======================================
    // HASIL UPDATE
    // ======================================

    if ($update) {

        header("Location: review.php");
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

    <title>Edit Data Review</title>


    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
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
            font-size: 14px;
            border-color: #ced4da;
            border-radius: 8px;
            padding-left: 15px;
            transition: all 0.3s ease;
        }

        .form-select, input.form-control {
            height: 48px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #1e7e34;
            box-shadow: 0 0 0 0.2rem rgba(30, 126, 52, 0.15);
        }

        /* Tombol Container di Kanan Bawah */
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

        <!-- JUDUL & DESKRIPSI -->
        <div class="form-title">
            Edit Data Review
        </div>
        <div class="form-subtitle">
            Perbarui penilaian atau komentar ulasan pelanggan di bawah ini.
        </div>


        <form method="POST">


            <!-- PILIH BOOKING -->

            <div class="mb-3">

                <label class="form-label">
                    Pilih Booking
                </label>

                <select name="booking_id" class="form-select" required>
                    <?php while ($row_booking = mysqli_fetch_assoc($query_booking)): ?>
                        <option value="<?= $row_booking['booking_id']; ?>" <?= ($row_booking['booking_id'] == $review['booking_id']) ? 'selected' : ''; ?>>
                            Booking #<?= $row_booking['booking_id']; ?> (Tgl Survey: <?= $row_booking['tanggal_survey']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>

            </div>


            <!-- RATING -->

            <div class="mb-3">

                <label class="form-label">
                    Rating (1 - 5)
                </label>

                <select name="rating" class="form-select" required>
                    <option value="5" <?= ($review['rating'] == 5) ? 'selected' : ''; ?>>5 - Sangat Puas</option>
                    <option value="4" <?= ($review['rating'] == 4) ? 'selected' : ''; ?>>4 - Puas</option>
                    <option value="3" <?= ($review['rating'] == 3) ? 'selected' : ''; ?>>3 - Cukup</option>
                    <option value="2" <?= ($review['rating'] == 2) ? 'selected' : ''; ?>>2 - Kurang</option>
                    <option value="1" <?= ($review['rating'] == 1) ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                </select>

            </div>


            <!-- KOMENTAR -->

            <div class="mb-3">

                <label class="form-label">
                    Komentar
                </label>

                <textarea
                    name="komentar"
                    class="form-control"
                    rows="4"
                    style="height: auto;"
                    required
                ><?= htmlspecialchars($review['komentar']); ?></textarea>

            </div>


            <!-- TOMBOL (DI KANAN BAWAH) -->

            <div class="tombol-container">

                <a
                    href="review.php"
                    class="btn btn-kembali"
                >
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>

                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-tambah"
                >
                    <i class="bi bi-check-lg"></i> Simpan
                </button>

            </div>


        </form>

    </div>

</div>


</body>

</html>