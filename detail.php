<?php
// Aktifkan error reporting untuk mendeteksi pesan kesalahan jika ada
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/database.php';

// Menyesuaikan koneksi database
if (!isset($db) && isset($conn)) {
    $db = $conn;
} elseif (!isset($db) && isset($koneksi)) {
    $db = $koneksi;
}

if (!isset($db) || mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

/*
|--------------------------------------------------------------------------
| TANGKAP ID KOS
|--------------------------------------------------------------------------
*/
$kos_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($kos_id <= 0) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| QUERY DETAIL KOS
|--------------------------------------------------------------------------
*/
$query_kos = "
    SELECT k.*, u.nama AS nama_owner, u.no_wa
    FROM kos k
    LEFT JOIN user u ON u.user_id = k.owner_id
    WHERE k.kos_id = '$kos_id'
";
$res_kos = mysqli_query($db, $query_kos);
$kos = $res_kos ? mysqli_fetch_assoc($res_kos) : null;

if (!$kos) {
    die("<h2 style='text-align:center; margin-top:50px; font-family:Poppins, sans-serif; color:#2C3E35;'>Data Kos tidak ditemukan.</h2>");
}

/*
|--------------------------------------------------------------------------
| QUERY FOTO, HARGA, FASILITAS & RATING
|--------------------------------------------------------------------------
*/
$res_foto = mysqli_query($db, "SELECT * FROM foto_kos WHERE kos_id = '$kos_id' ORDER BY foto_id ASC");
$fotos = [];
if ($res_foto) {
    while ($f = mysqli_fetch_assoc($res_foto)) {
        $fotos[] = $f['path_foto'];
    }
}

$res_harga = mysqli_query($db, "SELECT MIN(harga) AS harga_mulai FROM kamar WHERE kos_id = '$kos_id'");
$harga_data = $res_harga ? mysqli_fetch_assoc($res_harga) : null;
$harga_mulai = $harga_data['harga_mulai'] ?? 0;

$res_fas = mysqli_query($db, "
    SELECT f.nama_fasilitas
    FROM kos_fasilitas kf
    JOIN fasilitas f ON kf.fasilitas_id = f.fasilitas_id
    WHERE kf.kos_id = '$kos_id'
");

// Ambil daftar kamar berdasarkan kos_id untuk pilihan form booking
$res_kamar = mysqli_query($db, "SELECT * FROM kamar WHERE kos_id = '$kos_id'");

// Stat Rating & Total Ulasan
$res_review = mysqli_query($db, "
    SELECT AVG(r.rating) as avg_rating, COUNT(r.review_id) as total_review
    FROM review r
    JOIN booking b ON r.booking_id = b.booking_id
    JOIN kamar km ON b.kamar_id = km.kamar_id
    WHERE km.kos_id = '$kos_id'
");
$review_data = $res_review ? mysqli_fetch_assoc($res_review) : null;
$rating = ($review_data && $review_data['avg_rating']) ? round($review_data['avg_rating'], 1) : '0';
$total_review = $review_data['total_review'] ?? 0;

/*
|--------------------------------------------------------------------------
| QUERY AMBIL DAFTAR ULASAN DARI USER
|--------------------------------------------------------------------------
*/
$res_list_review = mysqli_query($db, "
    SELECT r.*, u.nama AS nama_user
    FROM review r
    JOIN booking b ON r.booking_id = b.booking_id
    JOIN kamar km ON b.kamar_id = km.kamar_id
    JOIN user u ON b.user_id = u.user_id
    WHERE km.kos_id = '$kos_id'
    ORDER BY r.dibuat_pada DESC
");

/*
|--------------------------------------------------------------------------
| CEK ID BOOKING USER (JIKA ADA)
|--------------------------------------------------------------------------
*/
$user_booking_id = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $q_booking = mysqli_query($db, "
        SELECT b.booking_id 
        FROM booking b
        JOIN kamar km ON b.kamar_id = km.kamar_id
        WHERE km.kos_id = '$kos_id' AND b.user_id = '$user_id'
        ORDER BY b.booking_id DESC LIMIT 1
    ");
    if ($q_booking && $row_b = mysqli_fetch_assoc($q_booking)) {
        $user_booking_id = $row_b['booking_id'];
    }
}

$no_wa = $kos['no_wa'] ?? '';
if (!empty($no_wa) && substr($no_wa, 0, 1) === '0') {
    $no_wa = '62' . substr($no_wa, 1);
}

$default_image = 'assets/images/default-kos.jpg';

// Data pengguna untuk navbar
$user_login_id = $_SESSION['user_id'] ?? null;
$user_data_session = [];
if ($user_login_id) {
    $q_user = mysqli_query($db, "SELECT * FROM user WHERE user_id = '$user_login_id'");
    if ($q_user) {
        $user_data_session = mysqli_fetch_assoc($q_user);
    }
}

$foto_session = (!empty($_SESSION['foto']) && file_exists('uploads/' . $_SESSION['foto']))
    ? 'uploads/' . $_SESSION['foto'] . '?v=' . time()
    : 'uploads/default.jpeg';

// Fungsi Rupiah
function rupiah($angka) {
    return ($angka > 0) ? 'Rp ' . number_format((int)$angka, 0, ',', '.') : 'Harga belum tersedia';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail <?= htmlspecialchars($kos['nama_kos']); ?> - AKSARA KOST</title>
    <link rel="icon" href="uploads/logokost.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* =================================
            RESET DAN DASAR (SAGE GREEN THEME)
        ================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; background: #FAF9F6; color: #2C3E35; min-height: 100vh; }
        a { text-decoration: none; }

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

        /* =================================
            NAVBAR
        ================================= */
        .navbar { background: rgba(250,249,246,0.96); backdrop-filter: blur(15px); border-bottom: 1px solid var(--sage-border); position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .navbar-container { width: 100%; max-width: 1300px; margin: auto; padding: 14px 25px; display: flex; align-items: center; justify-content: space-between; }
        
        .logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon { width: 34px; height: 34px; object-fit: contain; }
        .logo-text-group { display: flex; flex-direction: column; line-height: 1.1; }
        .logo-title { color: var(--sage-dark); font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .logo-subtitle { color: var(--sage-dark); font-size: 14px; font-weight: 700; }

        .nav-menu { display: flex; gap: 30px; align-items: center; }
        .nav-menu a { color: var(--muted); font-size: 15px; font-weight: 800; transition: .3s; }
        .nav-menu a:hover, .nav-menu a.active-link { color: var(--sage-dark); }

        .btn-login {
            background: var(--sage);
            color: #fff !important;
            padding: 8px 22px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 13px;
            transition: 0.3s;
        }
        .btn-login:hover { background: var(--sage-dark); }

        .btn-profile {
            background: var(--sage-soft);
            color: var(--sage-dark) !important;
            border: 1px solid var(--sage-border);
            padding: 5px 15px 5px 5px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 9px;
            font-weight: 700 !important;
        }
        .navbar-profile-img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--sage); }

        /* =================================
            LAYOUT MAIN CONTAINER
        ================================= */
        .container { 
            max-width: 1100px; 
            margin: 35px auto; 
            background: #fff; 
            padding: 35px; 
            border-radius: 22px; 
            border: 1px solid var(--sage-border); 
            box-shadow: 0 5px 20px rgba(135,169,135,0.06); 
        }
        .detail-grid { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 35px; align-items: start; }

        /* GALERI FOTO */
        .main-img { width: 100%; height: 320px; object-fit: cover; border-radius: 16px; background: var(--sage-soft); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .thumbs { display: flex; gap: 10px; margin-top: 12px; overflow-x: auto; padding-bottom: 5px; }
        .thumbs img { width: 85px; height: 70px; object-fit: cover; border-radius: 12px; cursor: pointer; border: 2px solid var(--sage-border); transition: 0.3s; }
        .thumbs img:hover { border-color: var(--sage); transform: scale(1.03); }

        /* DETAIL INFORMASI */
        .title { font-size: 24px; color: var(--text); margin-bottom: 6px; font-weight: 800; }
        .lokasi { font-size: 13px; color: var(--muted); margin-bottom: 14px; line-height: 1.5; }
        .lokasi i { color: var(--sage); }
        .harga { font-size: 24px; font-weight: 800; color: var(--sage-dark); margin-bottom: 6px; }
        .harga span { font-size: 13px; color: var(--muted); font-weight: normal; }
        .rating { font-size: 13px; color: #f59e0b; font-weight: 700; margin-bottom: 20px; }

        /* FASILITAS */
        .fasilitas-title { font-size: 15px; font-weight: 800; color: var(--text); margin-bottom: 12px; border-top: 1px solid var(--sage-border); padding-top: 18px; }
        .fasilitas-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px; color: var(--muted); font-weight: 500; }

        /* FORM BOOKING LANGSUNG */
        .booking-box { margin-top: 25px; background: var(--sage-soft); padding: 22px; border-radius: 18px; border: 1px solid var(--sage-border); }
        .booking-box label { font-size: 13px; font-weight: 700; color: var(--sage-dark); display: block; margin-bottom: 8px; }
        .booking-box select, .booking-box input[type="date"] { width: 100%; padding: 12px 16px; border: 1px solid var(--sage-border); border-radius: 12px; outline: none; margin-bottom: 14px; font-size: 13px; background: #fff; font-family: 'Poppins', sans-serif; color: var(--text); }
        .btn-booking { width: 100%; padding: 12px; background: var(--sage); color: white; border: none; border-radius: 30px; font-weight: 700; cursor: pointer; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 14px rgba(135,169,135,0.2); }
        .btn-booking:hover { background: var(--sage-dark); transform: translateY(-1px); }

        /* DESKRIPSI & TOMBOL */
        .deskripsi { grid-column: 1 / -1; margin-top: 30px; border-top: 1px solid var(--sage-border); padding-top: 22px; }
        .deskripsi h3 { font-size: 17px; margin-bottom: 10px; color: var(--text); font-weight: 800; }
        .deskripsi p { font-size: 14px; color: var(--muted); line-height: 1.8; }

        .actions { grid-column: 1 / -1; display: flex; gap: 15px; margin-top: 20px; }
        .btn-action { flex: 1; padding: 12px 20px; border-radius: 30px; font-weight: 700; text-align: center; font-size: 13px; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-hubungi { background: #25D366; color: #fff; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.2); }
        .btn-hubungi:hover { background: #1ebd5a; transform: translateY(-1px); }
        .btn-tandai { background: var(--sage-soft); color: var(--sage-dark); border: 1px solid var(--sage-border); }
        .btn-tandai:hover { background: var(--sage); color: #fff; }

        /* BAGIAN ULASAN / REVIEW */
        .review-section { grid-column: 1 / -1; margin-top: 35px; border-top: 1px solid var(--sage-border); padding-top: 25px; }
        .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .review-header h3 { font-size: 18px; color: var(--text); font-weight: 800; }
        
        .review-box { background: #fdfafd; border: 1px solid var(--sage-border); padding: 16px 20px; border-radius: 16px; margin-bottom: 12px; }
        .review-user { font-weight: 700; font-size: 13px; color: var(--text); display: flex; justify-content: space-between; }
        .review-stars { color: #f59e0b; font-size: 12px; margin: 6px 0; }
        .review-text { font-size: 13px; color: var(--muted); line-height: 1.6; }
        .review-date { font-size: 11px; color: var(--muted); font-weight: normal; }

        .form-review-box { background: var(--sage-soft); padding: 20px; border-radius: 18px; border: 1px solid var(--sage-border); margin-bottom: 22px; }
        .form-review-box label { font-size: 13px; font-weight: 700; color: var(--text); }
        .form-review-box select, .form-review-box textarea { width: 100%; padding: 10px 14px; margin-top: 6px; margin-bottom: 12px; border: 1px solid var(--sage-border); border-radius: 12px; font-size: 13px; outline: none; background: #fff; font-family: 'Poppins', sans-serif; color: var(--text); }
        .btn-submit-review { background: var(--sage); color: #fff; border: none; padding: 10px 22px; border-radius: 30px; font-weight: 700; cursor: pointer; font-size: 13px; transition: 0.3s; }
        .btn-submit-review:hover { background: var(--sage-dark); }

        @media (max-width: 768px) {
            .detail-grid { grid-template-columns: 1fr; }
            .navbar-container { padding: 12px 18px; }
            .nav-menu { display: none; }
            .container { padding: 20px 15px; margin: 20px auto; }
            .actions { flex-direction: column; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="logo">
            <img src="uploads/logokost.png" alt="Logo Aksara Kost" class="logo-icon">
            <div class="logo-text-group">
                <span class="logo-title">AKSARA</span>
                <span class="logo-subtitle">KOST</span>
            </div>
        </a>
        <div class="nav-menu">
            <a href="index.php">Beranda</a>
            <a href="daftarkos.php">Kost</a>
            <a href="index.php#tentang">Tentang</a>
        </div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profil.php" class="btn-profile">
                    <img src="<?= htmlspecialchars($foto_session); ?>" alt="Profil" class="navbar-profile-img" onerror="this.src='uploads/default.jpeg';">
                    <span><?= htmlspecialchars($user_data_session['nama'] ?? $_SESSION['nama'] ?? 'User'); ?></span>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- MAIN CONTAINER -->
<div class="container">
    <div class="detail-grid">
        
        <!-- KOLOM KIRI: GALERI FOTO -->
        <div>
            <?php $foto_utama = !empty($fotos) ? 'uploads/' . $fotos[0] : $default_image; ?>
            <img id="mainImage" src="<?= htmlspecialchars($foto_utama); ?>" class="main-img" onerror="this.src='<?= $default_image; ?>';">
            
            <?php if (!empty($fotos)): ?>
                <div class="thumbs">
                    <?php foreach ($fotos as $f): ?>
                        <img src="uploads/<?= htmlspecialchars($f); ?>" onclick="document.getElementById('mainImage').src=this.src;" onerror="this.src='<?= $default_image; ?>';">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- KOLOM KANAN: INFORMASI & FORM BOOKING TERHUBUNG KE KAMAR -->
        <div>
            <h1 class="title"><?= htmlspecialchars($kos['nama_kos']); ?></h1>
            <p class="lokasi"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($kos['alamat']); ?></p>

            <div class="harga">
                <?= $harga_mulai > 0 ? 'Rp ' . number_format($harga_mulai, 0, ',', '.') : 'Harga belum tersedia'; ?>
                <?php if ($harga_mulai > 0): ?><span>/ bulan</span><?php endif; ?>
            </div>

            <div class="rating">
                ★ <?= $rating; ?> <span style="color:var(--muted); font-weight:normal;">(<?= $total_review; ?> ulasan)</span>
            </div>

            <div class="fasilitas-title">Fasilitas Tersedia</div>
            <div class="fasilitas-grid">
                <?php if ($res_fas && mysqli_num_rows($res_fas) > 0): ?>
                    <?php while ($f = mysqli_fetch_assoc($res_fas)): ?>
                        <div><i class="fa-solid fa-check" style="color: var(--sage);"></i> <?= htmlspecialchars($f['nama_fasilitas']); ?></div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div><i class="fa-solid fa-check" style="color: var(--sage);"></i> WiFi</div>
                    <div><i class="fa-solid fa-check" style="color: var(--sage);"></i> AC</div>
                    <div><i class="fa-solid fa-check" style="color: var(--sage);"></i> Kamar Mandi Dalam</div>
                    <div><i class="fa-solid fa-check" style="color: var(--sage);"></i> Kasur & Lemari</div>
                <?php endif; ?>
            </div>

            <!-- FORM BOOKING TERHUBUNG KE TABEL KAMAR -->
            <form action="proses_booking.php" method="POST" class="booking-box">
                <input type="hidden" name="kos_id" value="<?= $kos['kos_id']; ?>">
                
                <!-- PILIH KAMAR -->
                <label><i class="fa-solid fa-bed"></i> Pilih Kamar:</label>
                <select name="kamar_id" required>
                    <option value="" disabled selected hidden>-- Pilih Kamar & Status --</option>
                    <?php if ($res_kamar && mysqli_num_rows($res_kamar) > 0): ?>
                        <?php while ($kamar = mysqli_fetch_assoc($res_kamar)): 
                            $status_kamar = trim($kamar['status_kamar']);
                            $is_tersedia = (strtolower($status_kamar) === 'kosong');
                            $label_status = $is_tersedia ? ' (Tersedia)' : ' (' . $status_kamar . ')';
                        ?>
                            <option value="<?= $kamar['kamar_id']; ?>" <?= !$is_tersedia ? 'disabled style="background:#f1f3f4; color:#999;"' : ''; ?>>
                                Kamar <?= htmlspecialchars($kamar['no_kamar']); ?> - <?= rupiah($kamar['harga']); ?><?= $label_status; ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="" disabled>Belum ada data kamar tersedia</option>
                    <?php endif; ?>
                </select>

                <!-- PILIH TANGGAL SURVEY -->
                <label><i class="fa-regular fa-calendar-days"></i> Pilih Tanggal Survey / Masuk:</label>
                <input type="date" name="tanggal_survey" required min="<?= date('Y-m-d'); ?>">

                <button type="submit" class="btn-booking">
                    <i class="fa-solid fa-clipboard-list"></i> Ajukan Booking Kost
                </button>
            </form>
        </div>

        <!-- DESKRIPSI -->
        <div class="deskripsi">
            <h3>Tentang Kost Ini</h3>
            <p>
                <?= htmlspecialchars($kos['nama_kos']); ?> merupakan kost nyaman dan bersih dengan lokasi strategis di <?= htmlspecialchars($kos['alamat']); ?>. 
                Sangat cocok untuk mahasiswa maupun pekerja. Pemilik kost: <strong><?= htmlspecialchars($kos['nama_owner'] ?? '-'); ?></strong>.
            </p>
        </div>

        <!-- TOMBOL AKSI LAIN -->
        <div class="actions">
            <?php if (!empty($no_wa)): ?>
                <a href="https://wa.me/<?= $no_wa; ?>?text=Halo,%20saya%20tertarik%20dengan%20<?= urlencode($kos['nama_kos']); ?>" target="_blank" class="btn-action btn-hubungi">
                    <i class="fa-brands fa-whatsapp"></i> Hubungi Pemilik via WA
                </a>
            <?php else: ?>
                <button class="btn-action btn-hubungi" style="background:#888;" onclick="alert('Nomor WA Pemilik belum tersedia.')"><i class="fa-brands fa-whatsapp"></i> Hubungi Pemilik</button>
            <?php endif; ?>
            <a href="favorit.php?tambah_id=<?= $_GET['id']; ?>" class="btn-action btn-tandai" style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;" onclick="alert('Kost berhasil ditambahkan ke favorit!');">
                <i class="fa-solid fa-heart" style="margin-right: 6px;"></i> Simpan Favorit
            </a>
        </div>

        <!-- SEKSI ULASAN & REVIEW -->
        <div class="review-section">
            <div class="review-header">
                <h3>💬 Ulasan & Review Penghuni</h3>
            </div>

            <!-- FORM TAMBAH REVIEW DARI USER -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="proses_review.php" method="POST" class="form-review-box">
                    <input type="hidden" name="kos_id" value="<?= $kos['kos_id']; ?>">
                    <input type="hidden" name="booking_id" value="<?= $user_booking_id; ?>">
                    
                    <label><strong>Beri Ulasan Kamu:</strong></label>
                    <select name="rating" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Bagus)</option>
                        <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                        <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                        <option value="2">⭐⭐ (2 - Buruk)</option>
                        <option value="1">⭐ (1 - Sangat Buruk)</option>
                    </select>

                    <textarea name="komentar" rows="3" placeholder="Tuliskan ulasan kamu tentang kost ini..." required></textarea>
                    
                    <button type="submit" name="submit_review" class="btn-submit-review">Kirim Ulasan</button>
                </form>
            <?php else: ?>
                <p style="font-size:13px; color:var(--muted); margin-bottom:15px; background:var(--sage-soft); padding:12px; border-radius:12px; border:1px solid var(--sage-border);">
                    Ingin memberikan ulasan? Silakan <a href="login.php" style="color:var(--sage-dark); font-weight:700;">Login</a> terlebih dahulu.
                </p>
            <?php endif; ?>

            <!-- DAFTAR ULASAN PENGGUNA -->
            <?php if ($res_list_review && mysqli_num_rows($res_list_review) > 0): ?>
                <?php while ($rev = mysqli_fetch_assoc($res_list_review)): ?>
                    <div class="review-box">
                        <div class="review-user">
                            <span><i class="fa-solid fa-user-circle" style="color: var(--sage);"></i> <?= htmlspecialchars($rev['nama_user']); ?></span>
                            <span class="review-date"><?= date('d M Y', strtotime($rev['dibuat_pada'])); ?></span>
                        </div>
                        <div class="review-stars">
                            <?= str_repeat('★', (int)$rev['rating']); ?><?= str_repeat('☆', 5 - (int)$rev['rating']); ?>
                        </div>
                        <div class="review-text">
                            "<?= htmlspecialchars($rev['komentar']); ?>"
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="font-size:13px; color:var(--muted); font-style:italic;">Belum ada ulasan untuk kost ini.</p>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>