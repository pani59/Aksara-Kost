<?php
session_start();
require_once __DIR__ . '/database.php';

$tahun_pilih = $_REQUEST['tahun'] ?? date('Y');
$format      = $_GET['format'] ?? '';
$act         = $_GET['act'] ?? ($_GET['export'] ?? $format);

$nama_bulan_indo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$pendapatan_bulanan = array_fill(1, 12, 0);

// Query data pendapatan per bulan
$query = "SELECT MONTH(b.dibuat_pada) AS bulan, SUM(k.harga) AS total 
          FROM booking b
          JOIN kamar k ON b.kamar_id = k.kamar_id
          WHERE b.status_booking IN ('Disetujui', 'Terisi')
          AND YEAR(b.dibuat_pada) = '$tahun_pilih'
          GROUP BY MONTH(b.dibuat_pada)";

$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pendapatan_bulanan[(int)$row['bulan']] = (float)$row['total'];
    }
}

$total_pendapatan = array_sum($pendapatan_bulanan);

/*
|--------------------------------------------------------------------------
| MODE EXCEL (Di-center & Baris Tinggi Panjang Ke Bawah)
|--------------------------------------------------------------------------
*/
if ($act === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Laporan Pendapatan" . $tahun_pilih . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; }
            .header-title { font-weight: bold; font-size: 14pt; text-align: center; border: none !important; height: 30pt; }
            .header-code { font-weight: bold; font-size: 11pt; text-align: center; color: #555; border: none !important; height: 25pt; }
            .no-border { border: none !important; }
            table.tb-data { border-collapse: collapse; margin: auto; }
            table.tb-data th, table.tb-data td { 
                border: 1px solid #000000; 
                padding: 12px 15px; 
                font-size: 11pt; 
                vertical-align: middle; 
            }
            table.tb-data tr { height: 28pt; } 
            table.tb-data th { 
                background-color: #f2f2f2; 
                font-weight: bold; 
                text-align: center; 
                height: 32pt; 
            }
            .text-center { text-align: center; mso-number-format: "\@"; }
            .text-left { text-align: left; }
            .text-right { text-align: right; mso-number-format: "\@"; }
            .total-row { background-color: #f2f2f2; font-weight: bold; height: 30pt; }
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
        <table class="tb-data">
            <tr>
                <td class="no-border" style="width: 50px;"></td>
                <td class="no-border" style="width: 50px;"></td>
                <td class="no-border" style="width: 50px;"></td>
                <td class="no-border" style="width: 50px;"></td>
                <td colspan="3" class="header-title">LAPORAN PENDAPATAN</td>
            </tr>
            <tr>
                <td class="no-border" colspan="4"></td>
                <td colspan="3" class="header-code">PENDAPATAN/<?= $tahun_pilih; ?></td>
            </tr>
            <tr><td colspan="7" class="no-border" style="height: 15pt;"></td></tr>
            <thead>
                <tr style="height: 32pt;">
                    <td class="no-border" colspan="4"></td>
                    <th style="width: 80px;">No.</th>
                    <th style="width: 220px;">Bulan</th>
                    <th style="width: 250px;">Nominal Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nama_bulan_indo as $num => $nama): ?>
                    <tr style="height: 28pt;">
                        <td class="no-border" colspan="4"></td>
                        <td class="text-center"><?= $num; ?>.</td>
                        <td class="text-left"><?= $nama; ?></td>
                        <td class="text-right">Rp <?= number_format($pendapatan_bulanan[$num], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row" style="height: 30pt;">
                    <td class="no-border" colspan="4"></td>
                    <td colspan="2" class="text-center">TOTAL PENDAPATAN:</td>
                    <td class="text-right">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pendapatan Bulanan - PENDAPATAN/<?= $tahun_pilih; ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { 
            font-family: 'Source Sans Pro', Arial, sans-serif; 
            margin: 0 auto; 
            padding: 30px;
            max-width: 800px;
            color: #333; 
            line-height: 1.5;
            background: #f4f7f6;
        }
        .action-bar {
            background: #ffffff;
            border: none;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            text-decoration: none;
            border: none;
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s;
        }
        .btn-pdf { background-color: #3b5343; }
        .btn-pdf:hover { background-color: #2c3e32; opacity: 0.9; color: #fff; }
        .btn-excel { background-color: #5d826c; }
        .btn-excel:hover { background-color: #4e705b; opacity: 0.9; color: #fff; }

        #dokumen-cetak {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .header { 
            text-align: center; 
            font-weight: bold; 
            font-size: 18px; 
            margin-bottom: 5px; 
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kode { 
            text-align: center; 
            font-weight: bold; 
            font-size: 14px; 
            color: #6c757d;
            margin-bottom: 25px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0 10px 0; 
        }
        table, th, td { 
            border: 1px solid #dee2e6; 
        }
        th, td { 
            padding: 12px 15px; 
            font-size: 14px; 
            vertical-align: middle;
        }
        th { 
            font-weight: bold; 
            background-color: #f8f9fa;
            text-align: center;
            color: #495057;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <span style="font-size: 14px; color: #6c757d; font-weight: 600;">Pilih Unduhan Laporan Pendapatan:</span>
        <div>
            <button onclick="downloadPDF()" class="btn-action btn-pdf"><i class="fas fa-file-pdf"></i><strong>Download PDF</strong></button>
            <a href="cetak_laporan.php?tahun=<?= $tahun_pilih; ?>&act=excel" class="btn-action btn-excel"><i class="fas fa-file-excel"></i><strong>Download Excel</strong></a>
        </div>
    </div>

    <div id="dokumen-cetak">
        <div class="header">
            LAPORAN PENDAPATAN 
        </div>
        
        <div class="kode">
            PENDAPATAN/<?= $tahun_pilih; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="10%">No.</th>
                    <th width="45%">Bulan</th>
                    <th width="45%">Nominal Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nama_bulan_indo as $num => $nama): ?>
                    <tr>
                        <td class="text-center"><?= $num; ?>.</td>
                        <td class="text-left"><?= $nama; ?></td>
                        <td class="text-right">Rp <?= number_format($pendapatan_bulanan[$num], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-center">TOTAL PENDAPATAN:</td>
                    <td class="text-right">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('dokumen-cetak');
            const opt = {
                margin:       10,
                filename:     'Laporan Pendapatan<?= $tahun_pilih; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
    </script>

</body>
</html>