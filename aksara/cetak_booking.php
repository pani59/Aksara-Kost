<?php
session_start();
require_once __DIR__ . '/../database.php';

// Menyesuaikan koneksi database
if (!isset($conn) && isset($db)) {
    $conn = $db;
} elseif (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

$booking_id = $_GET['id'] ?? '';
$format     = $_GET['format'] ?? '';
$act        = $_GET['act'] ?? $format;

// Query data booking lengkap
$query = mysqli_query($conn, "
    SELECT 
        b.booking_id, b.tanggal_survey, COALESCE(b.status_booking, 'Menunggu') AS status_booking,
        km.no_kamar, km.tipe_kamar, km.harga,
        k.nama_kos, k.alamat,
        u_pencari.nama AS nama_pencari,
        u_owner.nama AS nama_owner
    FROM booking b
    JOIN kamar km ON b.kamar_id = km.kamar_id
    JOIN kos k ON km.kos_id = k.kos_id
    JOIN user u_pencari ON b.user_id = u_pencari.user_id
    LEFT JOIN user u_owner ON k.owner_id = u_owner.user_id
    WHERE b.booking_id = '$booking_id'
");

$data = mysqli_fetch_assoc($query);

$st = strtolower(trim($data['status_booking'] ?? ''));
$is_approved = ($st === 'disetujui' || $st === 'approved' || $st === 'selesai');

if (!$data || !$is_approved) {
    echo "<script>alert('Bukti booking tidak dapat dicetak!'); window.close();</script>";
    exit;
}

// Membersihkan nomor kamar agar tidak double tulisan "No."
$no_kamar_raw = trim($data['no_kamar'] ?? '-');
$no_kamar_clean = preg_replace('/^no\.?\s*/i', '', $no_kamar_raw);

// Konversi tanggal ke format Indonesia
function tanggal_indo($tanggal){
    $bulan = [
        1 => 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
        'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
    ];
    $var = explode('-', $tanggal);
    return [
        'tgl' => $var[2] ?? date('d'),
        'bln' => $bulan[(int)($var[1] ?? date('m'))],
        'thn' => $var[0] ?? date('Y')
    ];
}

$tgl_booking = tanggal_indo(date('Y-m-d', strtotime($data['tanggal_survey'])));

/*
|--------------------------------------------------------------------------
| MODE EXCEL
|--------------------------------------------------------------------------
*/
if ($act === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Bukti Booking" . $data['booking_id'] . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: 'Poppins', Arial, sans-serif; }
            .header-title { font-weight: bold; font-size: 14pt; text-align: center; color: #2C3E35; }
            .header-code { font-weight: bold; font-size: 11pt; text-align: right; color: #6D8A6D; }
            .isi-text { font-size: 11pt; text-align: justify; color: #2C3E35; }
            table.tb-data { border-collapse: collapse; width: 100%; margin: auto; }
            table.tb-data th, table.tb-data td { border: 1px solid #D1E0D1; padding: 6px; font-size: 11pt; }
            table.tb-data th { background-color: #EBF2EB; font-weight: bold; text-align: center; color: #6D8A6D; }
            .ttd-title { font-weight: bold; text-align: center; font-size: 11pt; color: #2C3E35; }
            .ttd-nama { text-align: center; font-size: 11pt; color: #2C3E35; }
        </style>
    </head>
    <body>
        <table style="width: 100%;">
            <tr>
                <td colspan="6" align="right" class="header-code">KOS/BOOKING<?= $data['booking_id']; ?></td>
            </tr>
            <tr>
                <td colspan="6" align="center" class="header-title">BUKTI KONFIRMASI BOOKING<br>SURVEY/SEWA AKSARA KOST</td>
            </tr>
            <tr><td colspan="6"></td></tr>
            <tr>
                <td colspan="6" class="isi-text">
                    Pada hari ini tanggal <?= $tgl_booking['tgl']; ?> bulan <?= $tgl_booking['bln']; ?> tahun <?= $tgl_booking['thn']; ?>, telah dilakukan konfirmasi booking antara pihak <b>PEMILIK KOS</b> dengan <b>PENCARI KOS</b> melalui Aplikasi Pencarian dan Pemesanan Aksara Kost Berbasis Peta.
                </td>
            </tr>
            <tr><td colspan="6"></td></tr>
            <tr>
                <td colspan="6">Adapun rincian booking sebagai berikut :</td>
            </tr>
            <tr><td colspan="6"></td></tr>
        </table>

        <table class="tb-data">
            <thead>
                <tr>
                    <th width="8%">No.</th>
                    <th width="42%" colspan="2">Nama Kos</th>
                    <th width="32%" colspan="2">Kamar</th>
                    <th width="18%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td align="center">1.</td>
                    <td colspan="2"><?= htmlspecialchars($data['nama_kos']); ?></td>
                    <td colspan="2">No. <?= htmlspecialchars($no_kamar_clean); ?> - Tipe <?= htmlspecialchars($data['tipe_kamar'] ?? '-'); ?></td>
                    <td align="center"><?= htmlspecialchars($data['status_booking']); ?></td>
                </tr>
            </tbody>
        </table>

        <table style="width: 100%;">
            <tr><td colspan="6"></td></tr>
            <tr>
                <td colspan="6" class="isi-text">
                    Demikian bukti konfirmasi booking ini dibuat dengan sebenar-benarnya dan berlaku sebagai bukti sah persetujuan kedua belah pihak.
                </td>
            </tr>
            <tr><td colspan="6"></td></tr>
            <tr><td colspan="6"></td></tr>
            <tr>
                <td colspan="3" align="center" class="ttd-title">Pemilik Kos</td>
                <td colspan="3" align="center" class="ttd-title">Pencari Kos</td>
            </tr>
            <tr><td colspan="6" style="height: 35px;"></td></tr>
            <tr>
                <td colspan="3" align="center" class="ttd-nama">Nama: <?= htmlspecialchars($data['nama_owner'] ?? 'Admin Utama'); ?></td>
                <td colspan="3" align="center" class="ttd-nama">Nama: <?= htmlspecialchars($data['nama_pencari']); ?></td>
            </tr>
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
    <title>Bukti Konfirmasi Booking - KOS/BKG-01/BOOK00<?= $data['booking_id']; ?></title>
    <link rel="icon" href="../uploads/logokost.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        /* =================================
           RESET DAN DASAR (SAGE GREEN THEME)
        ================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Poppins', sans-serif; 
            margin: 0 auto; 
            padding: 30px 20px;
            max-width: 850px;
            color: #2C3E35; 
            line-height: 1.6;
            background: #FAF9F6;
        }

        :root {
            --sage: #87A987;
            --sage-dark: #6D8A6D;
            --sage-soft: #EBF2EB;
            --sage-light: #F4F7F4;
            --sage-border: #D1E0D1;
            --text: #2C3E35;
            --muted: #627268;
            --white: #ffffff;
            --shadow: 0 10px 35px rgba(135,169,135,0.12);
        }

        .action-bar {
            background: #ffffff;
            border: 1px solid var(--sage-border);
            padding: 15px 25px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }
        .btn-action {
            padding: 10px 20px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        .btn-pdf { 
            background-color: var(--sage); 
            box-shadow: 0 4px 14px rgba(135,169,135,0.2);
        }
        .btn-pdf:hover { background-color: var(--sage-dark); transform: translateY(-1px); }
        .btn-excel { 
            background-color: #ffffff; 
            color: var(--sage-dark); 
            border: 1px solid var(--sage-border);
        }
        .btn-excel:hover { background-color: var(--sage-soft); transform: translateY(-1px); }

        #dokumen-cetak {
            background: #ffffff;
            padding: 40px;
            border-radius: 22px;
            border: 1px solid var(--sage-border);
            box-shadow: 0 10px 35px rgba(135,169,135,0.08);
            position: relative;
        }
        .top-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }
        .kode { 
            font-weight: 700; 
            font-size: 12px; 
            color: var(--muted);
            background: var(--sage-soft);
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid var(--sage-border);
        }
        .header { 
            text-align: center; 
            font-weight: 800; 
            font-size: 19px; 
            color: var(--text);
            margin-bottom: 6px; 
            line-height: 1.3;
        }
        .sub-header-brand {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: var(--sage-dark);
            margin-bottom: 25px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .isi { 
            text-align: justify; 
            font-size: 14px; 
            margin-bottom: 20px; 
            color: var(--muted);
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0 25px 0; 
        }
        table, th, td { 
            border: 1px solid var(--sage-border); 
        }
        th, td { 
            padding: 12px 15px; 
            text-align: left; 
            font-size: 14px; 
        }
        th { 
            font-weight: 700; 
            background-color: var(--sage-soft);
            color: var(--sage-dark);
        }
        td {
            color: var(--text);
        }
        .ttd-container {
            width: 100%;
            margin-top: 50px;
        }
        .ttd-box { 
            width: 40%; 
            text-align: center; 
            float: left; 
            font-size: 14px;
            color: var(--text);
        }
        .ttd-box-right { 
            width: 40%; 
            text-align: center; 
            float: right; 
            font-size: 14px;
            color: var(--text);
        }
        .space { height: 60px; }
        .clearfix::after { 
            content: ""; 
            clear: both; 
            display: table; 
        }

        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none; }
            #dokumen-cetak { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <span style="font-size: 13px; color: var(--muted); font-weight: 600;">
            <i class="fa-solid fa-file-arrow-down" style="color: var(--sage);"></i> Pilih Format Unduhan Bukti Booking:
        </span>
        <div style="display: flex; gap: 10px;">
            <button onclick="downloadPDF()" class="btn-action btn-pdf">
                <i class="fa-solid fa-file-pdf"></i> Download PDF
            </button>
            <a href="cetak_booking.php?id=<?= $data['booking_id']; ?>&act=excel" class="btn-action btn-excel">
                <i class="fa-solid fa-file-excel"></i> Download Excel
            </a>
        </div>
    </div>

    <div id="dokumen-cetak">
        <div class="top-row">
            <div class="kode">
                KOS/BKG-01/BOOK00<?= $data['booking_id']; ?>
            </div>
        </div>
        
        <div class="header">
            BUKTI KONFIRMASI BOOKING<br>SURVEY / SEWA KOS
        </div>
        <div class="sub-header-brand">AKSARA KOST</div>

        <div class="isi">
            Pada hari ini tanggal <b><?= $tgl_booking['tgl']; ?></b> bulan <b><?= $tgl_booking['bln']; ?></b> tahun <b><?= $tgl_booking['thn']; ?></b>, telah dilakukan konfirmasi booking antara pihak <b>PEMILIK KOS</b> dengan <b>PENCARI KOS</b> melalui Aplikasi Pencarian dan Pemesanan Aksara Kost Berbasis Peta.
        </div>

        <p style="font-size: 14px; margin-bottom: 10px; font-weight: 700; color: var(--text);">Adapun rincian booking sebagai berikut:</p>

        <table>
            <thead>
                <tr>
                    <th width="8%" style="text-align: center;">No.</th>
                    <th>Nama Kos</th>
                    <th>Kamar</th>
                    <th width="18%" style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1.</td>
                    <td><b><?= htmlspecialchars($data['nama_kos']); ?></b></td>
                    <td>No. <?= htmlspecialchars($no_kamar_clean); ?> - Tipe <?= htmlspecialchars($data['tipe_kamar'] ?? '-'); ?></td>
                    <td style="text-align: center;">
                        <span style="background: var(--sage-soft); color: var(--sage-dark); padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 700;">
                            <?= htmlspecialchars($data['status_booking']); ?>
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="isi">
            Demikian bukti konfirmasi booking ini dibuat dengan sebenar-benarnya dan berlaku sebagai bukti sah persetujuan kedua belah pihak di lingkungan AKSARA KOST.
        </div>

        <div class="ttd-container clearfix">
            <div class="ttd-box">
                <b>Pemilik Kos</b>
                <div class="space"></div>
                <b><?= htmlspecialchars($data['nama_owner'] ?? 'Admin Utama'); ?></b>
            </div>
            <div class="ttd-box-right">
                <b>Pencari Kos</b>
                <div class="space"></div>
                <b><?= htmlspecialchars($data['nama_pencari'] ?? 'Pencari Kos'); ?></b>
            </div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('dokumen-cetak');
            const opt = {
                margin:       10,
                filename:     'Bukti_Booking_<?= $data['booking_id']; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
    </script>

</body>
</html>