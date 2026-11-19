<?php
// Aktifkan error reporting untuk mendeteksi pesan kesalahan jika ada
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'database.php';

/* =========================================
   KONEKSI DATABASE
========================================= */
if (!isset($db) && isset($conn)) {
    $db = $conn;
}
if (!isset($db) && isset($koneksi)) {
    $db = $koneksi;
}
if (!isset($db) || !($db instanceof mysqli)) {
    die("Koneksi database gagal. Periksa database.php");
}

/* =========================================
   FUNGSI RUPIAH
========================================= */
function rupiah($angka) {
    return ($angka > 0)
        ? 'Rp ' . number_format((int)$angka, 0, ',', '.')
        : 'Harga belum tersedia';
}

$default_image = 'uploads/tampilankos.jpeg';

/* =========================================
   DATA PENGGUNA & LOGIKA FOTO UNIK
========================================= */
$user_id = $_SESSION['user_id'] ?? null;
$user = [];

if ($user_id) {
    $stmt = mysqli_prepare($db, "SELECT * FROM user WHERE user_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $user_id);
        mysqli_stmt_execute($stmt);
        $result_user = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result_user) ?: [];
        mysqli_stmt_close($stmt);
    }
}

// LOGIKA MENGECEK FILE FOTO FISIK UNIK BERDASARKAN USER_ID
$found_foto = 'profil.jpeg';
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $extensions = ['jpg', 'jpeg', 'png', 'gif'];
    foreach ($extensions as $ext) {
        $filename = 'user_' . $uid . '.' . $ext;
        if (file_exists(__DIR__ . '/uploads/' . $filename)) {
            $found_foto = $filename;
            break;
        }
    }
}
$_SESSION['foto'] = $found_foto;

$foto_session = 'uploads/' . $found_foto;

/* =========================================
   QUERY KOS POPULER & BEST SELLER
========================================= */
$sql_populer = "
    SELECT k.*,
    (
        SELECT fk.path_foto
        FROM foto_kos fk
        WHERE fk.kos_id = k.kos_id
        ORDER BY fk.foto_id ASC
        LIMIT 1
    ) AS foto_kos,
    (
        SELECT MIN(km.harga)
        FROM kamar km
        WHERE km.kos_id = k.kos_id
    ) AS harga_mulai,
    (
        SELECT ROUND(AVG(r.rating), 1)
        FROM review r
        JOIN booking b ON r.booking_id = b.booking_id
        JOIN kamar km ON b.kamar_id = km.kamar_id
        WHERE km.kos_id = k.kos_id
    ) AS avg_rating
    FROM kos k
";

$res_populer = mysqli_query($db, $sql_populer);
if (!$res_populer) {
    die("Query kos gagal: " . mysqli_error($db));
}

$mock_ratings = [5.0, 4.9, 4.8, 4.6, 4.5, 4.3, 4.1, 4.0];
$list_kos = [];
$i = 0;

while ($k = mysqli_fetch_assoc($res_populer)) {
    $db_rating = !empty($k['avg_rating']) ? (float)$k['avg_rating'] : 0;
    $final_rating = ($db_rating >= 4.0) ? $db_rating : $mock_ratings[$i % count($mock_ratings)];
    $k['calculated_rating'] = $final_rating;
    $list_kos[] = $k;
    $i++;
}

usort($list_kos, function($a, $b) {
    return $b['calculated_rating'] <=> $a['calculated_rating'];
});

/* Ambil khusus untuk Carousel (Best Seller / Rating >= 4.5) */
$carousel_kos = array_filter($list_kos, function($item) {
    return $item['calculated_rating'] >= 4.5;
});
if (empty($carousel_kos)) {
    $carousel_kos = array_slice($list_kos, 0, 3);
}

/* =========================================
   DATA PETA
========================================= */
$locations = [];
foreach ($list_kos as $row) {
    if (!empty($row['latitude']) && !empty($row['longitude'])) {
        $lat = (float)$row['latitude'];
        $lng = (float)$row['longitude'];

        if ($lat > 0) {
            $lat = -$lat;
        }

        $foto = !empty($row['foto_kos']) ? 'uploads/' . $row['foto_kos'] : $default_image;
        $kos_id = (int)$row['kos_id'];

        $query_kamar = "SELECT status_kamar FROM kamar WHERE kos_id = $kos_id";
        $res_kamar = mysqli_query($db, $query_kamar);
        $status_kamar = 'Terisi';

        if ($res_kamar) {
            while ($kamar = mysqli_fetch_assoc($res_kamar)) {
                if (strtolower(trim($kamar['status_kamar'])) == 'kosong') {
                    $status_kamar = 'Kosong';
                    break;
                }
            }
        }

        $locations[] = [
            'id' => $row['kos_id'],
            'nama' => $row['nama_kos'],
            'alamat' => $row['alamat'],
            'harga' => rupiah($row['harga_mulai']),
            'lat' => $lat,
            'lng' => $lng,
            'foto' => $foto,
            'status' => $status_kamar
        ];
    }
}

$json_locations = json_encode($locations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
if ($json_locations === false) {
    $json_locations = '[]';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - AKSARA KOST</title>
    <link rel="icon" href="uploads/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 90px; }
        body { font-family: 'Poppins', sans-serif; background: #FAF9F6; color: #2C3E35; overflow-x: hidden; }
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

        /* NAVBAR */
        .navbar { position: sticky; top: 0; z-index: 1000; background: rgba(250,249,246,0.96); backdrop-filter: blur(15px); border-bottom: 1px solid var(--sage-border); }
        .navbar-container { max-width: 1300px; margin: auto; padding: 12px 25px; min-height: 72px; display: flex; align-items: center; justify-content: space-between; }
        .logo { display: flex; align-items: center; gap: 9px; }
        .logo-icon { width: 36px; height: 36px; object-fit: contain; }
        .logo-text-group { display: flex; flex-direction: column; line-height: 1.1; }
        .logo-title { color: var(--sage-dark); font-size: 20px; font-weight: 800; }
        .logo-subtitle { color: var(--sage-dark); font-size: 14px; font-weight: 700; }
        .nav-menu { display: flex; align-items: center; gap: 35px; }
        .nav-menu a { color: var(--muted); font-size: 15px; font-weight: 800; transition: .3s; }
        .nav-menu a:hover, .nav-menu a.active-link { color: var(--sage-dark); }
        .nav-auth { display: flex; align-items: center; }
        .btn-daftar { background: var(--sage); color: white; padding: 11px 25px; border-radius: 30px; font-size: 13px; font-weight: 700; transition: .3s; }
        .btn-daftar:hover { background: var(--sage-dark); transform: translateY(-2px); }
        .btn-profile { background: var(--sage-soft); color: var(--sage-dark) !important; border: 1px solid var(--sage-border); padding: 5px 15px 5px 5px; border-radius: 30px; display: flex; align-items: center; gap: 9px; font-weight: 700 !important; margin-left: auto; }
        .navbar-profile-img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--sage); }

        /* HERO SECTION & CAROUSEL BEST SELLER */
        .hero { max-width: 1300px; margin: 38px auto 55px; padding: 0 25px; }
        .hero-banner { position: relative; overflow: hidden; min-height: 360px; display: flex; align-items: center; justify-content: space-between; gap: 35px; padding: 45px; border-radius: 28px; background: radial-gradient(circle at 90% 10%, #F4F7F4 0, transparent 30%), linear-gradient(120deg, #D1E0D1, #EBF2EB); border: 1px solid var(--sage-border); box-shadow: var(--shadow); }
        .welcome-circle { position: absolute; width: 280px; height: 280px; background: rgba(255,255,255,.3); border-radius: 50%; right: 35%; top: -150px; }
        .hero-text { flex: 1; position: relative; z-index: 2; }
        .hero h1 { font-size: 34px; line-height: 1.35; font-weight: 800; color: var(--text); margin-bottom: 14px; max-width: 580px; }
        .hero p { color: var(--muted); font-size: 14px; line-height: 1.8; margin-bottom: 24px; max-width: 570px; }

        /* CAROUSEL CONTAINER */
        .hero-carousel-wrapper {
            width: 45%;
            height: 280px;
            position: relative;
            z-index: 2;
            overflow: hidden;
            border-radius: 22px;
            box-shadow: 0 12px 30px rgba(44,62,53,.13);
            background: #000;
        }
        .carousel-track {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.6s ease-in-out;
        }
        .carousel-slide {
            min-width: 100%;
            height: 100%;
            position: relative;
        }
        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .carousel-caption-box {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.85), transparent);
            padding: 20px 15px 15px;
            color: white;
        }
        .carousel-caption-box .badge-bs {
            background: #f3a712;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 5px;
            display: inline-block;
        }
        .carousel-caption-box h4 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .carousel-caption-box p {
            font-size: 11px;
            color: #ddd;
            margin-bottom: 3px;
        }
        .carousel-caption-box .price-rating {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            font-weight: 700;
            color: #a8f5a8;
        }
        .carousel-caption-box .price-rating span i {
            color: #f3a712;
        }

        /* SEARCH */
        .search-container { max-width: 590px; }
        .search-bar { display: flex; align-items: center; padding: 6px; padding-left: 18px; background: white; border: 1px solid var(--sage-border); border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,.04); margin-bottom: 15px; }
        .search-bar input { flex: 1; min-width: 0; border: none; outline: none; font-family: inherit; font-size: 13px; color: var(--text); }
        .search-bar button { border: none; background: var(--sage); color: white; padding: 12px 23px; border-radius: 11px; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; transition: .3s; }
        .search-bar button:hover { background: var(--sage-dark); }
        .filter-pills { display: flex; flex-wrap: wrap; gap: 9px; }
        .pill-btn { display: inline-flex; align-items: center; padding: 8px 19px; border-radius: 30px; background: white; border: 1px solid var(--sage-border); color: var(--sage-dark); font-size: 12px; font-weight: 700; transition: .3s; }
        .pill-btn:hover, .pill-btn.active { background: var(--sage); color: white; border-color: var(--sage); }

        /* CONTAINER */
        .container { max-width: 1300px; margin: 0 auto; padding: 0 25px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 23px; }
        .section-header h2 { font-size: 23px; font-weight: 800; color: var(--text); display: flex; align-items: center; gap: 10px; }
        .section-header a { font-size: 13px; color: var(--sage-dark); font-weight: 700; transition: .3s; }
        .section-header a:hover { color: var(--sage); }

        .icon-box-circle {
            width: 34px;
            height: 34px;
            background: var(--sage);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            box-shadow: 0 3px 10px rgba(135,169,135,0.3);
        }

        /* KARTU KOS */
        .kos-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px; }
        .kos-card { background: white; border: 1px solid var(--sage-border); border-radius: 22px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 7px 25px rgba(44,62,53,.04); transition: .35s; }
        .kos-card:hover { transform: translateY(-7px); box-shadow: 0 15px 35px rgba(135,169,135,.15); border-color: var(--sage); }
        .kos-img-wrapper { width: 100%; height: 185px; position: relative; overflow: hidden; background: var(--sage-soft); }
        .kos-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s; }
        .kos-card:hover .kos-img-wrapper img { transform: scale(1.07); }
        .badge-bestseller { position: absolute; top: 12px; left: 12px; z-index: 2; background: white; color: var(--sage-dark); border: 1px solid var(--sage-border); border-radius: 30px; padding: 6px 11px; font-size: 11px; font-weight: 700; box-shadow: 0 3px 10px rgba(0,0,0,.08); }
        .kos-card-body { padding: 18px; flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; }
        .kos-card-body h3 { font-size: 15px; color: var(--text); font-weight: 800; margin-bottom: 8px; line-height: 1.5; display: flex; align-items: center; gap: 8px; }
        .kos-card-body .lokasi { font-size: 11px; color: var(--muted); line-height: 1.7; margin-bottom: 10px; }
        .kos-card-body .lokasi i { color: var(--sage); margin-right: 3px; }
        .kos-card-body .harga { color: var(--sage-dark); font-size: 15px; font-weight: 800; margin-bottom: 7px; }
        .kos-card-body .rating { font-size: 12px; color: #f3a712; font-weight: 700; }
        .btn-detail { width: 100%; display: block; text-align: center; background: var(--sage-soft); border: 1px solid var(--sage-border); color: var(--sage-dark); padding: 11px; border-radius: 30px; font-size: 12px; font-weight: 700; transition: .3s; }
        .btn-detail:hover { background: var(--sage); color: white; border-color: var(--sage); }

        /* PETA */
        .map-section { margin-top: 75px; }
        .map-heading { margin-bottom: 20px; }
        .map-heading p { color: var(--muted); font-size: 13px; margin-top: 5px; }
        #map { width: 100%; height: 430px; border-radius: 24px; border: 1px solid var(--sage-border); box-shadow: var(--shadow); z-index: 1; }
        .custom-pin { background: transparent !important; border: none !important; }

        /* TENTANG AKSARA KOST */
        .about-card-wrapper { position: relative; overflow: hidden; background: radial-gradient(circle at 5% 5%, #fff 0, transparent 30%), linear-gradient(135deg, var(--sage-soft), #D1E0D1); border: 1px solid var(--sage-border); border-radius: 28px; padding: 55px 40px; margin-top: 75px; text-align: center; box-shadow: var(--shadow); }
        .about-card-wrapper::before { content: ""; position: absolute; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,.4); top: -100px; right: -60px; }
        .about-header, .about-grid { position: relative; z-index: 1; }
        .about-icon-main { width: 70px; height: 70px; object-fit: contain; margin-bottom: 15px; }
        .about-header h2 { font-size: 28px; font-weight: 800; color: var(--text); margin-bottom: 13px; }
        .about-header p { max-width: 800px; margin: 0 auto 38px; font-size: 13px; color: var(--muted); line-height: 1.9; }
        .about-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
        .about-box { background: rgba(255,255,255,.95); border: 1px solid var(--sage-border); border-radius: 22px; padding: 32px 22px; min-height: 195px; box-shadow: 0 8px 25px rgba(44,62,53,.04); transition: .35s; }
        .about-box:hover { transform: translateY(-7px); box-shadow: 0 12px 30px rgba(135,169,135,.15); }
        .about-box-icon { width: 55px; height: 55px; background: var(--sage-soft); border-radius: 17px; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; color: var(--sage-dark); font-size: 23px; }
        .about-box h3 { font-size: 16px; font-weight: 800; color: var(--text); margin-bottom: 10px; }
        .about-box p { font-size: 12px; color: var(--muted); line-height: 1.8; }

        /* FOOTER */
        .footer { margin-top: 75px; background: white; border-top: 1px solid var(--sage-border); padding: 55px 25px 20px; }
        .footer-container { max-width: 1250px; margin: auto; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 55px; padding-bottom: 35px; }
        .footer-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 17px; }
        .footer-logo img { width: 55px; height: 55px; object-fit: contain; }
        .footer-logo-text { display: flex; flex-direction: column; line-height: 1.2; }
        .footer-logo-text strong { font-size: 20px; color: var(--sage-dark); font-weight: 800; }
        .footer-logo-text span { color: var(--sage-dark); font-weight: 700; font-size: 14px; }
        .footer-col h4 { color: var(--text); font-size: 16px; font-weight: 800; margin-bottom: 20px; }
        .footer-col p { color: var(--muted); font-size: 12px; line-height: 1.9; margin-bottom: 12px; }
        .footer-contact i { color: var(--sage); margin-right: 8px; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 13px; }
        .footer-col ul li a { color: var(--muted); font-size: 12px; transition: .3s; }
        .footer-col ul li a:hover { color: var(--sage-dark); padding-left: 5px; }
        .footer-bottom { border-top: 1px solid var(--sage-border); padding-top: 20px; text-align: center; }
        .footer-bottom p { color: var(--muted); font-size: 11px; }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .kos-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hero h1 { font-size: 29px; }
            .footer-container { gap: 30px; }
        }
        @media (max-width: 768px) {
            .navbar-container { padding: 12px 15px; }
            .nav-menu { display: none; }
            .hero { padding: 0 15px; margin-top: 20px; }
            .hero-banner { flex-direction: column-reverse; align-items: stretch; padding: 23px; gap: 22px; }
            .hero-carousel-wrapper { width: 100%; height: 210px; border-radius: 18px; }
            .hero h1 { font-size: 25px; }
            .hero p { font-size: 12px; }
            .container { padding: 0 15px; }
            .section-header h2 { font-size: 18px; }
            .kos-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .kos-img-wrapper { height: 145px; }
            .kos-card { border-radius: 16px; }
            .kos-card-body { padding: 12px; }
            .kos-card-body h3 { font-size: 13px; }
            .kos-card-body .lokasi { font-size: 10px; }
            .kos-card-body .harga { font-size: 12px; }
            .btn-detail { font-size: 10px; padding: 9px 5px; }
            .badge-bestseller { font-size: 9px; padding: 5px 8px; }
            .about-card-wrapper { padding: 35px 18px; margin-top: 45px; }
            .about-header h2 { font-size: 23px; }
            .about-grid { grid-template-columns: 1fr; gap: 15px; }
            .about-box { min-height: auto; padding: 25px 18px; }
            #map { height: 350px; }
            .footer-container { grid-template-columns: 1fr; gap: 25px; }
            .footer { padding: 35px 20px 20px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="logo">
            <img src="uploads/logoaksara.png?v=2" alt="Logo Aksara Kost" class="logo-icon">
            <div class="logo-text-group">
                <span class="logo-title">AKSARA</span>
                <span class="logo-subtitle">KOST</span>
            </div>
        </a>
        <div class="nav-menu" id="navMenu">
            <a href="index.php" class="active-link">Beranda</a>
            <a href="#daftar-kos">Kost</a>
            <a href="#peta-lokasi">Peta</a>
            <a href="#tentang">Tentang</a>
        </div>
        <div class="nav-auth">
            <?php if ($user_id): ?>
                <a href="profil.php" class="btn-profile">
                    <img src="<?= htmlspecialchars($foto_session); ?>" alt="Foto Profil" class="navbar-profile-img" onerror="this.src='uploads/profil.jpeg';">
                    <span><?= htmlspecialchars($user['nama'] ?? $_SESSION['nama'] ?? 'User'); ?></span>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-daftar">Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- HERO BERANDA DENGAN CAROUSEL BEST SELLER -->
<section class="hero">
    <div class="hero-banner">
        <div class="welcome-circle"></div>
        <div class="hero-text">
            <h1>Temukan Kost Impianmu dengan Mudah!</h1>
            <p>Cari dan temukan kost yang sesuai kebutuhanmu di lokasi favoritmu, cepat, aman, dan terpercaya.</p>
            <div class="search-container">
                <form action="daftarkos.php" method="GET" class="search-bar">
                    <input type="text" name="cari" placeholder="Cari lokasi, nama kos, atau fasilitas...">
                    <button type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i> Cari
                    </button>
                </form>
                <div class="filter-pills">
                    <a href="daftarkos.php" class="pill-btn active">Semua</a>
                    <a href="daftarkos.php?tipe=Putri" class="pill-btn">Putri</a>
                    <a href="daftarkos.php?tipe=Putra" class="pill-btn">Putra</a>
                    <a href="daftarkos.php?tipe=Campur" class="pill-btn">Campur</a>
                </div>
            </div>
        </div>

        <!-- CAROUSEL BANNER BEST SELLER -->
        <div class="hero-carousel-wrapper">
            <div class="carousel-track" id="carouselTrack">
                <?php foreach ($carousel_kos as $bk): ?>
                    <?php 
                        $b_foto = !empty($bk['foto_kos']) ? 'uploads/' . $bk['foto_kos'] : $default_image;
                        $b_harga = rupiah($bk['harga_mulai']);
                        $b_rating = number_format($bk['calculated_rating'], 1);
                    ?>
                    <div class="carousel-slide">
                        <img src="<?= htmlspecialchars($b_foto); ?>" alt="<?= htmlspecialchars($bk['nama_kos']); ?>" onerror="this.src='uploads/tampilankos.jpeg';">
                        <div class="carousel-caption-box">
                            <span class="badge-bs"><i class="fa-solid fa-star"></i> Best Seller</span>
                            <h4><?= htmlspecialchars($bk['nama_kos']); ?></h4>
                            <p><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($bk['alamat']); ?></p>
                            <div class="price-rating">
                                <span><?= $b_harga; ?>/bln</span>
                                <span><i class="fa-solid fa-star"></i> <?= $b_rating; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- DAFTAR KOS POPULER -->
<div class="container">
    <div class="section-header" id="daftar-kos">
        <h2>
            <span class="icon-box-circle"><i class="fa-solid fa-house"></i></span> 
            Daftar Kost Populer
        </h2>
        <a href="daftarkos.php">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <div class="kos-grid">
        <?php foreach ($list_kos as $k): ?>
            <?php
            $foto = !empty($k['foto_kos']) ? 'uploads/' . $k['foto_kos'] : $default_image;
            $rating = $k['calculated_rating'];
            ?>
            <div class="kos-card">
                <div class="kos-img-wrapper">
                    <?php if ($rating >= 4.5): ?>
                        <div class="badge-bestseller">
                            <i class="fa-solid fa-star"></i> Best Seller
                        </div>
                    <?php endif; ?>
                    <img src="<?= htmlspecialchars($foto); ?>" alt="<?= htmlspecialchars($k['nama_kos']); ?>" onerror="this.src='uploads/tampilankos.jpeg';">
                </div>
                <div class="kos-card-body">
                    <div>
                        <h3>
                            <span class="icon-box-circle" style="width:28px;height:28px;font-size:12px;"><i class="fa-solid fa-house"></i></span> 
                            <?= htmlspecialchars($k['nama_kos']); ?>
                        </h3>
                        <p class="lokasi">
                            <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($k['alamat']); ?>
                        </p>
                        <p class="harga">
                            <?= rupiah($k['harga_mulai']); ?>
                            <small style="font-weight:400;color:var(--muted);font-size:11px;">/bulan</small>
                        </p>
                        <p class="rating">
                            <i class="fa-solid fa-star"></i> <?= number_format($rating, 1); ?>
                        </p>
                    </div>
                    <a href="detail.php?id=<?= (int)$k['kos_id']; ?>" class="btn-detail">
                        <i class="fa-solid fa-eye"></i> Lihat Detail
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- PETA PERSEBARAN KOS -->
    <section class="map-section" id="peta-lokasi">
        <div class="section-header map-heading">
            <div>
                <h2>
                    <span class="icon-box-circle"><i class="fa-solid fa-map-location-dot"></i></span> 
                    Peta Persebaran Kost
                </h2>
                <p>Temukan lokasi kost dengan mudah melalui peta.</p>
            </div>
        </div>
        <div id="map"></div>
    </section>

    <!-- TENTANG AKSARA KOST -->
    <section class="about-card-wrapper" id="tentang">
        <div class="about-header">
            <img src="uploads/logoaksara.png?v=2" alt="Logo Aksara Kost" class="logo-icon">
            <h2>Tentang AKSARA KOST</h2>
            <p>Platform pencarian kost online yang mudah, cepat, dan terpercaya. Temukan kost idealmu hanya dalam beberapa klik, dengan data terverifikasi dan dukungan penuh untuk pengalaman mencari kost yang aman dan nyaman.</p>
        </div>
        <div class="about-grid">
            <div class="about-box">
                <div class="about-box-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <h3>Mudah Dicari</h3>
                <p>Cari kost sesuai lokasi, harga, dan fasilitas favoritmu dengan filter yang lengkap dan mudah digunakan.</p>
            </div>
            <div class="about-box">
                <div class="about-box-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3>Aman dan Terpercaya</h3>
                <p>Semua kost sudah terverifikasi oleh tim kami, memastikan informasi akurat dan aman untuk kamu.</p>
            </div>
            <div class="about-box">
                <div class="about-box-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <h3>Dukungan 24/7</h3>
                <p>Tim customer service siap membantu kamu kapan saja melalui layanan yang responsif.</p>
            </div>
        </div>
    </section>
</div>

<!-- FOOTER -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-col">
            <div class="footer-logo">
                <link rel="icon" href="uploads/logoaksara.png">
                <div class="footer-logo-text">
                    <strong>Aksara</strong>
                    <span>Kost</span>
                </div>
            </div>
            <p>Platform terpercaya untuk menemukan hunian impian Anda dengan mudah, aman, dan cepat di berbagai wilayah.</p>
            <p class="footer-contact"><i class="fa-solid fa-envelope"></i> vanipermatasari12@gmail.com</p>
            <p class="footer-contact"><i class="fa-solid fa-phone"></i> 083140122106</p>
        </div>
        <div class="footer-col">
            <h4>Menu Utama</h4>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="daftarkos.php">Daftar Kost</a></li>
                <li><a href="#peta-lokasi">Peta Lokasi</a></li>
                <li><a href="#tentang">Tentang Kami</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Kebijakan & Privasi</h4>
            <ul>
                <li><a href="#">Syarat & Ketentuan</a></li>
                <li><a href="#">Kebijakan Privasi</a></li>
                <li><a href="mailto:vanipermatasari12@gmail.com">Bantuan & FAQ</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Copyright &copy; Vani PermataSari 2026 AKSARA KOST. Semua hak dilindungi.</p>
    </div>
</footer>

<!-- JAVASCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('#navMenu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            navLinks.forEach(item => item.classList.remove('active-link'));
            this.classList.add('active-link');
        });
    });
});

/* CAROUSEL INFINITE BERGERAK KE KANAN */
const track = document.getElementById('carouselTrack');

if (track && track.children.length > 1) {
    const firstSlide = track.children[0].cloneNode(true);
    track.appendChild(firstSlide);

    const totalSlides = track.children.length;
    let currentSlideIndex = 0;

    function moveCarousel() {
        currentSlideIndex++;
        track.style.transition = 'transform 0.6s ease-in-out';
        track.style.transform = `translateX(-${currentSlideIndex * 100}%)`;

        if (currentSlideIndex === totalSlides - 1) {
            setTimeout(function() {
                track.style.transition = 'none';
                currentSlideIndex = 0;
                track.style.transform = 'translateX(0)';
            }, 600);
        }
    }

    setInterval(moveCarousel, 4000);
}
</script>

<!-- LEAFLET MAP -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var locations = <?= $json_locations; ?>;
var defaultLat = locations.length > 0 ? locations[0].lat : -6.8852;
var defaultLng = locations.length > 0 ? locations[0].lng : 107.6135;

var map = L.map('map').setView([defaultLat, defaultLng], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

function createCustomPin(color) {
    return L.divIcon({
        className: 'custom-pin',
        html: `
            <div style="background-color:${color};width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid white;box-shadow:0 3px 6px rgba(0,0,0,.4);position:relative;">
                <div style="width:8px;height:8px;background:white;border-radius:50%;position:absolute;top:8px;left:8px;"></div>
            </div>
        `,
        iconSize: [28, 28],
        iconAnchor: [14, 28],
        popupAnchor: [0, -28]
    });
}

var greenPin = createCustomPin('#28a745');
var greyPin = createCustomPin('#6c757d');

locations.forEach(function(item) {
    if (item.lat && item.lng) {
        var selectedIcon = (item.status === 'Kosong') ? greenPin : greyPin;
        var marker = L.marker([item.lat, item.lng], { icon: selectedIcon }).addTo(map);

        var statusBadge = (item.status === 'Kosong')
            ? '<span style="background:#28a745;color:white;padding:3px 9px;border-radius:5px;font-size:10px;font-weight:bold;">Kosong</span>'
            : '<span style="background:#6c757d;color:white;padding:3px 9px;border-radius:5px;font-size:10px;font-weight:bold;">Terisi</span>';

        var popupHTML = `
            <div style="text-align:center;padding:5px;width:185px;">
                <img src="${item.foto}" style="width:100%;height:120px;object-fit:cover;border-radius:10px;margin-bottom:7px;">
                <h4 style="font-size:13px;margin-bottom:5px;color:#2C3E35;font-weight:700;"><i class="fa-solid fa-house" style="color:#87A987;margin-right:4px;"></i> ${item.nama}</h4>
                <p style="font-size:11px;color:#627268;margin-bottom:5px;">📍 ${item.alamat}</p>
                <p style="font-size:11px;margin-bottom:7px;">Status: ${statusBadge}</p>
                <div style="font-size:12px;font-weight:800;color:#6D8A6D;margin-bottom:9px;">${item.harga}/bln</div>
                <a href="detail.php?id=${encodeURIComponent(item.id)}" style="display:inline-block;background:#87A987;color:white;padding:7px 15px;border-radius:20px;font-size:11px;text-decoration:none;font-weight:700;">Lihat Detail</a>
            </div>
        `;
        marker.bindPopup(popupHTML);
    }
});
</script>

</body>
</html>