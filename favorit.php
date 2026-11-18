<?php
session_start();
require_once 'database.php';

// Menyesuaikan koneksi database menggunakan variabel $db
if (!isset($db) && isset($conn)) {
    $db = $conn;
} elseif (!isset($db) && isset($koneksi)) {
    $db = $koneksi;
}

if (!isset($db) || !$db) {
    die("Koneksi database gagal. Periksa kembali file database.php.");
}

// Pengecekan login dinonaktifkan agar bisa langsung dibuka tanpa login
/*
if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true) {
    header("Location: login.php");
    exit;
}
*/

// Menggunakan user_id dari sesi jika ada, atau default ke 1 jika belum login
$user_id = $_SESSION['user_id'] ?? 1;

// Inisialisasi session array untuk menyimpan ID kos yang dihapus khusus user ini
if (!isset($_SESSION['deleted_favorites'])) {
    $_SESSION['deleted_favorites'] = [];
}

// Proses Hapus Favorit (Langsung menghapus tanpa konfirmasi)
if (isset($_GET['hapus_id'])) {
    $kos_id_hapus = intval($_GET['hapus_id']);
    if (!in_array($kos_id_hapus, $_SESSION['deleted_favorites'])) {
        $_SESSION['deleted_favorites'][] = $kos_id_hapus;
    }
    header("Location: favorit.php");
    exit;
}

// Mengambil data pengguna
$stmt_user = mysqli_prepare($db, "SELECT * FROM user WHERE user_id = ?");
mysqli_stmt_bind_param($stmt_user, "s", $user_id);
mysqli_stmt_execute($stmt_user);

$query_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($query_user);

if (!$user) {
    // Data cadangan jika user_id belum ada di database
    $user = ['nama' => 'Vany', 'role' => 'User'];
}

// Fungsi keamanan output
function e($value) {
    return htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8');
}

// Foto profil
$foto_user = (!empty($_SESSION['foto']) && file_exists('../uploads/' . $_SESSION['foto']))
    ? '../uploads/' . $_SESSION['foto'] . '?v=' . time()
    : '../uploads/default.jpeg';

// Mengambil data kos dari database
$sql_fav = "
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
    ) AS harga_mulai
    FROM kos k
    LIMIT 3
";

$res_fav = mysqli_query($db, $sql_fav);

if (!$res_fav) {
    die("Gagal mengambil data kos: " . mysqli_error($db));
}

$default_image = '../assets/images/default-kos.jpg';

// Menampung data kos yang belum dihapus oleh user
$daftar_kos_favorit = [];
while ($f = mysqli_fetch_assoc($res_fav)) {
    if (!in_array($f['kos_id'], $_SESSION['deleted_favorites'])) {
        $daftar_kos_favorit[] = $f;
    }
}
$jumlah_kos = count($daftar_kos_favorit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kos Favorit Saya - AKSARA KOST</title>
    <link rel="icon" href="../uploads/logokost.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* =================================
            RESET DAN DASAR (HIJAU SAGE PASTEL)
        ================================= */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; background: #FAF9F6; color: #2C3E35; min-height: 100vh; }
        a { text-decoration: none; }

        :root {
            --primary: #87A987;
            --primary-dark: #6D8A6D;
            --primary-soft: #EBF2EB;
            --primary-light: #F4F7F4;
            --primary-border: #D1E0D1;
            --text: #2C3E35;
            --muted: #627268;
            --white: #ffffff;
            --shadow: 0 10px 35px rgba(135,169,135,0.08);
        }

        /* =================================
            NAVBAR DENGAN TOMBOL KEMBALI
        ================================= */
        .navbar { background: rgba(250,249,246,0.96); backdrop-filter: blur(15px); border-bottom: 1px solid var(--primary-border); position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .navbar-container { width: 100%; max-width: 1300px; margin: auto; padding: 14px 25px; display: flex; align-items: center; justify-content: space-between; position: relative; }
        
        .logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon { width: 34px; height: 34px; object-fit: contain; }
        .logo-text-group { display: flex; flex-direction: column; line-height: 1.1; }
        .logo-title { color: var(--primary-dark); font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .logo-subtitle { color: var(--primary-dark); font-size: 14px; font-weight: 700; }
        
        .nav-center { 
            position: absolute; 
            left: 50%; 
            transform: translateX(-50%); 
        }
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-dark);
            background: var(--primary-soft);
            border: 1px solid var(--primary-border);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            transition: 0.3s;
        }
        .btn-back-home:hover {
            background: var(--primary);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-profile {
            background: var(--primary-soft);
            color: var(--primary-dark) !important;
            border: 1px solid var(--primary-border);
            padding: 5px 15px 5px 5px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 9px;
            font-weight: 700 !important;
            margin-left: auto;
        }

        .navbar-profile-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        /* =================================
            LAYOUT UTAMA
        ================================= */
        .layout {
            max-width: 1300px;
            margin: 35px auto;
            padding: 0 25px;
            display: grid;
            grid-template-columns: 285px 1fr;
            gap: 28px;
            align-items: start;
        }

        /* =================================
            SIDEBAR
        ================================= */
        .sidebar {
            background: #ffffff;
            border: 1px solid var(--primary-border);
            border-radius: 22px;
            padding: 28px 20px 0;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }

        .sidebar-user {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--primary-soft);
        }

        .avatar-container {
            width: 110px;
            height: 110px;
            margin: 0 auto 13px;
            border-radius: 50%;
            border: 4px solid var(--primary);
            padding: 3px;
            overflow: hidden;
        }
        .avatar-container img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

        .sidebar-user strong { display: block; font-size: 18px; color: var(--text); margin-bottom: 4px; }
        .sidebar-user span { color: var(--muted); font-size: 13px; }

        .menu-list { list-style: none; margin-top: 20px; }
        .menu-list li { margin-bottom: 8px; }
        .menu-list a {
            display: flex; align-items: center; gap: 13px;
            padding: 13px 17px; color: var(--muted); font-size: 14px; font-weight: 600;
            border-radius: 12px; transition: 0.3s;
        }
        .menu-list a:hover, .menu-list a.active {
            background: var(--primary-soft); color: var(--primary-dark); font-weight: 700;
        }
        .menu-icon { font-size: 18px; width: 25px; text-align: center; color: var(--primary); }

        .sidebar-decoration {
            margin: 30px -20px 0; padding: 25px 10px 15px;
            text-align: center; background: linear-gradient(180deg, #fff 0%, var(--primary-soft) 100%);
            font-size: 12px; color: var(--primary-dark); font-weight: 600;
        }

        /* =================================
            MAIN CONTENT
        ================================= */
        .main-content { min-width: 0; }

        .welcome-banner {
            min-height: 170px; padding: 30px 38px; border-radius: 22px;
            background: linear-gradient(120deg, var(--primary-soft), #D1E0D1);
            border: 1px solid var(--primary-border); display: flex; align-items: center;
            justify-content: space-between; overflow: hidden; position: relative; margin-bottom: 22px;
        }
        .welcome-text { position: relative; z-index: 2; }
        .welcome-text h1 { font-size: 28px; color: var(--text); margin-bottom: 8px; font-weight: 800; }
        .welcome-text p { color: var(--muted); font-size: 14px; }
        
        .welcome-icon {
            position: relative;
            z-index: 2;
            margin-right: 20px;
        }
        .welcome-icon img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            opacity: 0.9;
        }

        .welcome-circle { position: absolute; width: 240px; height: 240px; background: rgba(255,255,255,0.3); border-radius: 50%; right: 100px; top: 35px; }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }
        .section-heading h2 { font-size: 22px; color: var(--text); font-weight: 800; }

        .total-favorit {
            background: var(--primary-soft);
            color: var(--primary-dark);
            border: 1px solid var(--primary-border);
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
        }

        /* KARTU KOS FAVORIT */
        .fav-list { display: flex; flex-direction: column; gap: 22px; }
        .fav-item {
            background: #ffffff; 
            border: 1px solid var(--primary-border);
            border-radius: 22px; 
            padding: 30px; 
            display: grid;
            grid-template-columns: 220px minmax(0, 1fr) 210px;
            gap: 25px; 
            align-items: center; 
            transition: 0.3s;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }
        .fav-item:hover { transform: translateY(-3px); border-color: var(--primary); box-shadow: 0 8px 22px rgba(135,169,135,0.12); }

        .fav-image { position: relative; overflow: hidden; border-radius: 16px; height: 155px; background: #D1E0D1; }
        .fav-image img { width: 100%; height: 100%; object-fit: cover; transition: 0.4s; }
        .fav-item:hover .fav-image img { transform: scale(1.06); }

        .logo-badge {
            position: absolute; top: 10px; right: 10px; width: 34px; height: 34px;
            display: flex; justify-content: center; align-items: center;
        }
        .logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .fav-info { min-width: 0; }
        .fav-info h3 { font-size: 20px; color: var(--text); margin-bottom: 8px; font-weight: 800; }
        .fav-address { font-size: 13px; color: var(--muted); margin-bottom: 12px; line-height: 1.5; }

        .fav-facilities { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 12px; }
        .facility-tag { background: var(--primary-soft); color: var(--primary-dark); padding: 6px 12px; border-radius: 30px; font-size: 11px; font-weight: 600; }

        .fav-price { color: var(--primary); font-size: 18px; font-weight: 800; }
        .fav-price small { font-size: 12px; font-weight: normal; color: var(--muted); }

        /* Tombol Aksi */
        .fav-actions { border-left: 1px solid var(--primary-border); padding-left: 22px; display: flex; flex-direction: column; gap: 10px; }
        .btn-detail {
            display: block; text-align: center; background: var(--primary-soft); color: var(--primary-dark);
            border: 1px solid var(--primary-border); padding: 11px 15px; border-radius: 30px;
            font-size: 13px; font-weight: 700; transition: 0.3s;
        }
        .btn-detail:hover { background: var(--primary); color: white; }

        .btn-hapus {
            display: block; text-align: center;
            background: #ffffff; border: 1px solid var(--primary-border); color: #475569;
            padding: 11px 15px; border-radius: 30px; font-size: 12px; font-weight: 700;
            cursor: pointer; transition: 0.3s;
        }
        .btn-hapus:hover { background: #fff0f0; color: #dc3545; border-color: #fca5a5; }

        .empty-favorite { background: #ffffff; border: 1px solid var(--primary-border); border-radius: 22px; text-align: center; padding: 50px 20px; }
        .empty-icon { font-size: 60px; color: var(--primary); margin-bottom: 15px; }
        .empty-favorite h3 { color: var(--text); font-size: 20px; margin-bottom: 8px; font-weight: 800; }
        .empty-favorite p { color: var(--muted); font-size: 14px; margin-bottom: 20px; }
        .btn-cari {
            display: inline-block; background: var(--primary); color: white;
            padding: 12px 24px; border-radius: 30px; font-size: 13px; font-weight: 700; transition: 0.3s;
            box-shadow: 0 6px 16px rgba(135,169,135,0.15);
        }
        .btn-cari:hover { background: var(--primary-dark); transform: translateY(-2px); }

        @media (max-width: 1000px) {
            .layout { grid-template-columns: 240px 1fr; gap: 20px; }
            .fav-item { grid-template-columns: 160px minmax(0, 1fr); }
            .fav-actions { grid-column: 2; border-left: none; border-top: 1px solid var(--primary-border); padding: 15px 0 0; flex-direction: row; }
            .fav-actions > * { flex: 1; }
        }

        @media (max-width: 768px) {
            .navbar-container { padding: 12px 18px; flex-wrap: wrap; gap: 10px; justify-content: space-between; }
            .nav-center { position: static; transform: none; order: 3; width: 100%; text-align: center; }
            .btn-profile { margin-left: 0; order: 2; }
            .layout { grid-template-columns: 1fr; padding: 0 15px; margin: 20px auto; }
            .sidebar-decoration { display: none; }
            .menu-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .menu-list li { margin: 0; }
            .welcome-banner { padding: 25px 20px; min-height: 140px; }
            .welcome-text h1 { font-size: 22px; }
            .welcome-icon { display: none; }
            .fav-item { grid-template-columns: 1fr; padding: 22px; }
            .fav-image { height: 180px; }
        }

        @media (max-width: 480px) {
            .welcome-banner { min-height: 120px; }
            .welcome-text h1 { font-size: 18px; }
            .fav-actions { flex-direction: column; }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="beranda.php" class="logo">
            <img src="../uploads/logokost.png" alt="Logo AKSARA KOST" class="logo-icon" onerror="this.src='../uploads/tampilankos.jpeg';">
            <div class="logo-text-group">
                <span class="logo-title">AKSARA</span>
                <span class="logo-subtitle">KOST</span>
            </div>
        </a>

        <div class="nav-center">
            <a href="beranda.php" class="btn-back-home">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <a href="profil.php" class="btn-profile">
            <img
                src="<?= e($foto_user); ?>"
                alt="Profil"
                class="navbar-profile-img"
                onerror="this.src='../uploads/default.jpeg';"
            >
            <span><?= e($user['nama']); ?></span>
        </a>
    </div>
</nav>

<!-- LAYOUT -->
<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="avatar-container">
                <img
                    src="<?= e($foto_user); ?>"
                    alt="Foto Profil"
                    onerror="this.src='../uploads/default.jpeg';"
                >
            </div>
            <strong><?= e($user['nama']); ?></strong>
            <span><?= e($user['role'] ?? 'User'); ?></span>
        </div>

        <ul class="menu-list">
            <li>
                <a href="profil.php">
                    <span class="menu-icon"><i class="fa-solid fa-house"></i></span>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="favorit.php" class="active">
                    <span class="menu-icon"><i class="fa-solid fa-heart"></i></span>
                    Favorit
                </a>
            </li>
            <li>
                <a href="booking.php">
                    <span class="menu-icon"><i class="fa-solid fa-clipboard-list"></i></span>
                    Pesanan
                </a>
            </li>
            <li>
                <a href="tentang.php">
                    <span class="menu-icon"><i class="fa-solid fa-circle-info"></i></span>
                    Tentang
                </a>
            </li>
        </ul>

        <div class="sidebar-decoration">
            <p>Temukan Kost Impianmu!</p>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- BANNER FAVORIT -->
        <section class="welcome-banner">
            <div class="welcome-circle"></div>
            <div class="welcome-text">
                <h1>♥ Kost Favorit Saya</h1>
                <p>Daftar kost pilihan yang sudah kamu sukai.</p>
            </div>
            <div class="welcome-icon">
                <img src="../uploads/logokost.png" alt="Logo AKSARA KOST" onerror="this.src='../uploads/tampilankos.jpeg';">
            </div>
        </section>

        <!-- JUDUL DAFTAR FAVORIT -->
        <div class="section-heading">
            <h2>Daftar Kos Favorit</h2>
            <span class="total-favorit">
                <?= $jumlah_kos; ?> Kos
            </span>
        </div>

        <!-- DAFTAR KOS -->
        <?php if ($jumlah_kos > 0): ?>

            <div class="fav-list">
                <?php foreach ($daftar_kos_favorit as $f): ?>
                    <?php
                    $foto = !empty($f['foto_kos'])
                        ? '../uploads/' . $f['foto_kos']
                        : $default_image;

                    $harga = $f['harga_mulai'];
                    ?>

                    <article class="fav-item">
                        <!-- FOTO KOS -->
                        <div class="fav-image">
                            <img
                                src="<?= e($foto); ?>"
                                alt="<?= e($f['nama_kos']); ?>"
                                onerror="this.onerror=null; this.src='../uploads/default-kos.jpg';"
                            >
                            <div class="logo-badge">
                                <img src="../uploads/logokost.png" alt="Logo AKSARA KOST" onerror="this.src='../uploads/tampilankos.jpeg';">
                            </div>
                        </div>

                        <!-- INFORMASI KOS -->
                        <div class="fav-info">
                            <h3><?= e($f['nama_kos']); ?></h3>
                            <p class="fav-address">
                                <i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> <?= e($f['alamat']); ?>
                            </p>

                            <div class="fav-facilities">
                                <span class="facility-tag">
                                    <i class="fa-solid fa-house"></i> Informasi Kost
                                </span>
                                <span class="facility-tag">
                                    <i class="fa-solid fa-heart"></i> Favorit
                                </span>
                            </div>

                            <div class="fav-price">
                                <?php if ($harga !== null && $harga !== ''): ?>
                                    Rp <?= number_format((float)$harga, 0, ',', '.'); ?>
                                    <small>/bulan</small>
                                <?php else: ?>
                                    <small>Harga belum tersedia</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI -->
                        <div class="fav-actions">
                            <a href="detail.php?id=<?= $f['kos_id']; ?>" class="btn-detail">
                                <i class="fa-solid fa-eye"></i> Lihat Detail
                            </a>
                            <a href="favorit.php?hapus_id=<?= $f['kos_id']; ?>" class="btn-hapus">
                                <i class="fa-solid fa-trash-can"></i> Hapus Favorit
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

        <?php else: ?>

            <!-- JIKA BELUM ADA KOS -->
            <div class="empty-favorite">
                <div class="empty-icon">
                    <i class="fa-regular fa-heart"></i>
                </div>
                <h3>Belum Ada Kost Favorit</h3>
                <p>Kamu belum menyimpan kost favorit. Yuk, cari kost yang sesuai dengan keinginanmu!</p>
                <a href="daftarkos.php" class="btn-cari">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari Kost Sekarang
                </a>
            </div>

        <?php endif; ?>

    </main>
</div>

</body>
</html>