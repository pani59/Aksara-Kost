<?php
session_start();
require_once '../database.php';

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

        <!-- HEADER / JUDUL CARD -->
        <div class="card-header-custom">
            Edit Data Kamar
        </div>

        <!-- BODY CARD -->
        <div class="card-body-custom">
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

    </div>

</div>

</body>
</html>