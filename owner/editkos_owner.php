<?php
require_once '../database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// CEK PARAMETER ID KOS
// ==========================================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Kos tidak ditemukan.");
}

$kos_id = intval($_GET['id']);

// ==========================================
// AMBIL DATA KOS BERDASARKAN ID
// ==========================================
$query = mysqli_query($conn, "SELECT * FROM kos WHERE kos_id = $kos_id");
if (!$query) {
    die("Query error: " . mysqli_error($conn));
}

$data_kos = mysqli_fetch_assoc($query);

if (!$data_kos) {
    die("Data kos tidak ditemukan di database.");
}

// ==========================================
// PROSES UPDATE KOS
// ==========================================
if (isset($_POST['simpan'])) {

    $nama_kos          = mysqli_real_escape_string($conn, trim($_POST['nama_kos']));
    $alamat            = mysqli_real_escape_string($conn, trim($_POST['alamat']));
    $latitude          = mysqli_real_escape_string($conn, trim($_POST['latitude']));
    $longitude         = mysqli_real_escape_string($conn, trim($_POST['longitude']));
    $tipe_kos          = mysqli_real_escape_string($conn, trim($_POST['tipe_kos']));
    $status_verifikasi = mysqli_real_escape_string($conn, trim($_POST['status_verifikasi']));

    $update = "UPDATE kos SET
                nama_kos          = '$nama_kos',
                alamat            = '$alamat',
                latitude          = '$latitude',
                longitude         = '$longitude',
                tipe_kos          = '$tipe_kos',
                status_verifikasi = '$status_verifikasi'
               WHERE kos_id = $kos_id";

    if (mysqli_query($conn, $update)) {
        header("Location: kos_owner.php"); 
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
    <title>Edit Data Kos</title>
    <link rel="icon" href="../assets/img/logoaksara.png">

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body {
            margin: 0;
            background-color: #f4f6f9;
            font-family: Arial, Helvetica, sans-serif;
        }

        .content {
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            padding: 55px 20px 50px 20px;
        }

        /* Styling untuk Card */
        .card-custom {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header-custom {
            font-size: 28px;
            font-weight: 600;
            color: #343a40;
            padding: 20px 25px;
            border-bottom: 1px solid #dee2e6;
            background-color: #ffffff;
        }

        .card-body-custom {
            padding: 30px 25px;
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

        textarea.form-control {
            height: auto;
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
    :root{--sage:#81c784;--sage-dark:#137333;--sage-soft:#e6f4ea;--page:#f8faf9;--ink:#2c3e50;--muted:#64748b;--line:#edf2f0;}
    html,body{font-family:'Source Sans Pro',Arial,sans-serif!important;color:var(--ink)!important;}
    body{background:var(--page)!important;}
    .btn-success,.btn-simpan{background:var(--sage)!important;border-color:var(--sage)!important;color:#fff!important;}
    .btn-success:hover,.btn-simpan:hover{background:#66bb6a!important;border-color:#66bb6a!important;}
    .form-control,.custom-select{border:1px solid #dfe7e2!important;border-radius:7px!important;box-shadow:none!important;}
    .form-control:focus{border-color:var(--sage)!important;box-shadow:0 0 0 .15rem rgba(129,199,132,.18)!important;}
    label{color:#405247;font-weight:600;}
    </style>
</head>

<body>

<!-- CONTENT -->
<div class="content">

    <!-- BUNGKUS FORM DALAM CARD -->
    <div class="card-custom">
        
        <!-- JUDUL / HEADER CARD -->
        <div class="card-header-custom">
            Edit Data Kos
        </div>

        <!-- ISI FORM -->
        <div class="card-body-custom">
            <form method="POST">

                <!-- NAMA KOS -->
                <div class="form-group">
                    <label>Nama Kos</label>
                    <input type="text" name="nama_kos" class="form-control" value="<?= htmlspecialchars($data_kos['nama_kos']); ?>" required>
                </div>

                <!-- ALAMAT -->
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($data_kos['alamat']); ?></textarea>
                </div>

                <!-- LATITUDE -->
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="text" name="latitude" class="form-control" value="<?= htmlspecialchars($data_kos['latitude']); ?>">
                </div>

                <!-- LONGITUDE -->
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="text" name="longitude" class="form-control" value="<?= htmlspecialchars($data_kos['longitude']); ?>">
                </div>

                <!-- TIPE KOS -->
                <div class="form-group">
                    <label>Tipe Kos</label>
                    <select name="tipe_kos" class="form-control" required>
                        <option value="Putra" <?= ($data_kos['tipe_kos'] == 'Putra') ? 'selected' : ''; ?>>Putra</option>
                        <option value="Putri" <?= ($data_kos['tipe_kos'] == 'Putri') ? 'selected' : ''; ?>>Putri</option>
                        <option value="Campur" <?= ($data_kos['tipe_kos'] == 'Campur') ? 'selected' : ''; ?>>Campur</option>
                    </select>
                </div>

                <!-- STATUS VERIFIKASI -->
                <div class="form-group">
                    <label>Status Verifikasi</label>
                    <select name="status_verifikasi" class="form-control" required>
                        <option value="Menunggu" <?= ($data_kos['status_verifikasi'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Aktif" <?= ($data_kos['status_verifikasi'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Non-aktif" <?= ($data_kos['status_verifikasi'] == 'Non-aktif') ? 'selected' : ''; ?>>Non-aktif</option>
                    </select>
                </div>

                <!-- BUTTON -->
                <div class="tombol">
                    <button type="submit" name="simpan" class="btn btn-simpan">
                        Simpan
                    </button>

                    <a href="kos_owner.php" class="btn btn-kembali ml-2">
                        Kembali
                    </a>
                </div>

            </form>
        </div>

    </div>

</div>

</body>
</html>