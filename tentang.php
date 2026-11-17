<?php
session_start();
require_once __DIR__ . 'database.php';

// Menyesuaikan koneksi database jika diperlukan
if (!isset($db) && isset($conn)) {
    $db = $conn;
} elseif (!isset($db) && isset($koneksi)) {
    $db = $koneksi;
}

$user_id = $_SESSION['user_id'] ?? null;
$user = [];

if ($user_id && $db) {
    $stmt_user = mysqli_prepare($db, "SELECT * FROM user WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt_user, "s", $user_id);
    mysqli_stmt_execute($stmt_user);
    $query_user = mysqli_stmt_get_result($stmt_user);
    $user = mysqli_fetch_assoc($query_user);
}

// Fungsi keamanan output
function e($value) {
    return htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8');
}

// Foto profil session / database
$foto_session = (!empty($_SESSION['foto']) && file_exists('uploads/' . $_SESSION['foto']))
    ? 'uploads/' . $_SESSION['foto'] . '?v=' . time()
    : 'uploads/default.jpeg';

$nama_user = $user['nama'] ?? $_SESSION['nama'] ?? 'User';
$foto_profil = e($foto_session);
$nama_tampil = e($nama_user);
$role_user = e($user['role'] ?? 'pengguna');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - AKSARA KOST</title>
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
           NAVBAR DENGAN TOMBOL KEMBALI
        ================================= */
        .navbar { background: rgba(250,249,246,0.96); backdrop-filter: blur(15px); border-bottom: 1px solid var(--sage-border); position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .navbar-container { width: 100%; max-width: 1300px; margin: auto; padding: 14px 25px; display: flex; align-items: center; justify-content: space-between; position: relative; }
        
        .logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon { width: 34px; height: 34px; object-fit: contain; }
        .logo-text-group { display: flex; flex-direction: column; line-height: 1.1; }
        .logo-title { color: var(--sage-dark); font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .logo-subtitle { color: var(--sage-dark); font-size: 14px; font-weight: 700; }
        
        .nav-center { 
            position: absolute; 
            left: 50%; 
            transform: translateX(-50%); 
        }
        .btn-back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--sage-dark);
            background: var(--sage-soft);
            border: 1px solid var(--sage-border);
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            transition: 0.3s;
        }
        .btn-back-home:hover {
            background: var(--sage);
            color: #ffffff;
            transform: translateY(-1px);
        }

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
            margin-left: auto;
        }

        .navbar-profile-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--sage);
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
            border: 1px solid var(--sage-border);
            border-radius: 22px;
            padding: 28px 20px 0;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }

        .sidebar-user {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--sage-soft);
        }

        .avatar-container {
            width: 110px;
            height: 110px;
            margin: 0 auto 13px;
            border-radius: 50%;
            border: 4px solid var(--sage);
            padding: 3px;
            overflow: hidden;
        }
        .avatar-container img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

        .sidebar-user strong { display: block; font-size: 18px; color: var(--text); margin-bottom: 4px; }
        .sidebar-user span { color: var(--muted); font-size: 13px; text-transform: capitalize; }

        .menu-list { list-style: none; margin-top: 20px; }
        .menu-list li { margin-bottom: 8px; }
        .menu-list a {
            display: flex; align-items: center; gap: 13px;
            padding: 13px 17px; color: var(--muted); font-size: 14px; font-weight: 600;
            border-radius: 12px; transition: 0.3s;
        }
        .menu-list a:hover, .menu-list a.active {
            background: var(--sage-soft); color: var(--sage-dark); font-weight: 700;
        }
        .menu-icon { font-size: 18px; width: 25px; text-align: center; color: var(--sage); }

        .sidebar-decoration {
            margin: 30px -20px 0; padding: 25px 10px 15px;
            text-align: center; background: linear-gradient(180deg, #fff 0%, var(--sage-soft) 100%);
            font-size: 12px; color: var(--sage-dark); font-weight: 600;
        }

        /* =================================
           MAIN CONTENT
        ================================= */
        .main-content { min-width: 0; }

        /* WELCOME BANNER */
        .welcome-banner {
            min-height: 170px; padding: 30px 38px; border-radius: 22px;
            background: linear-gradient(120deg, #D1E0D1, #EBF2EB);
            border: 1px solid var(--sage-border); display: flex; align-items: center;
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

        /* KARTU DESKRIPSI & KONTEN */
        .content-card {
            background: #ffffff;
            border: 1px solid var(--sage-border);
            border-radius: 22px;
            padding: 30px;
            margin-bottom: 22px;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }
        .content-card p {
            font-size: 15px;
            line-height: 1.8;
            color: var(--muted);
        }

        /* KEUNGGULAN (GRID 3) */
        .feature-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-bottom: 22px;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid var(--sage-border);
            border-radius: 22px;
            padding: 30px 22px;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }
        .feature-card:hover {
            transform: translateY(-3px);
            border-color: var(--sage);
            box-shadow: 0 8px 22px rgba(135,169,135,0.12);
        }

        .feature-icon {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: var(--sage-soft);
            border: 1px solid var(--sage-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: var(--sage-dark);
            margin: 0 auto 15px;
        }

        .feature-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--text);
            font-weight: 800;
        }

        .feature-card p {
            font-size: 13px;
            line-height: 1.6;
            color: var(--muted);
        }

        /* VISI MISI */
        .visi-misi {
            display: grid;
            grid-template-columns: 1fr 1.3fr;
            gap: 22px;
            margin-bottom: 22px;
        }

        .vm-card {
            background: #ffffff;
            border: 1px solid var(--sage-border);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
            display: flex;
            flex-direction: column;
        }

        .vm-header {
            background: var(--sage-soft);
            padding: 18px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--sage-border);
            color: var(--sage-dark);
            font-size: 20px;
        }

        .vm-header h3 {
            color: var(--text);
            font-size: 19px;
            font-weight: 800;
        }

        .vm-body {
            padding: 25px;
            flex: 1;
        }

        .vm-body p,
        .vm-body li {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
        }

        .vm-body ul {
            list-style: none;
        }

        .vm-body li {
            margin-bottom: 12px;
            position: relative;
            padding-left: 26px;
        }

        .vm-body li::before {
            content: "✓";
            position: absolute;
            left: 0;
            top: 2px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--sage);
            color: white;
            font-weight: bold;
            font-size: 11px;
        }

        /* FOOTER KONTEN */
        .footer-card {
            background: #ffffff;
            border: 1px solid var(--sage-border);
            border-radius: 22px;
            padding: 25px 30px;
            display: flex;
            align-items: center;
            gap: 30px;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }

        .footer-logo-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-right: 30px;
            border-right: 2px solid var(--sage-border);
        }

        .footer-logo-box img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .footer-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .footer-brand-title {
            color: var(--sage-dark);
            font-size: 18px;
            font-weight: 800;
        }
        .footer-brand-subtitle {
            color: var(--sage-dark);
            font-size: 13px;
            font-weight: 700;
        }

        .footer-info h4 {
            margin-bottom: 6px;
            color: var(--text);
            font-size: 16px;
            font-weight: 800;
        }

        .footer-info p {
            font-size: 13px;
            color: var(--muted);
        }

        /* =================================
           RESPONSIVE DESIGN
        ================================= */
        @media (max-width: 1000px) {
            .layout { grid-template-columns: 240px 1fr; gap: 20px; }
            .feature-wrapper { grid-template-columns: repeat(2, 1fr); }
            .visi-misi { grid-template-columns: 1fr; }
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
            .feature-wrapper { grid-template-columns: 1fr; }
            .footer-card { flex-direction: column; align-items: flex-start; gap: 15px; }
            .footer-logo-box { border-right: none; padding-right: 0; }
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

        <div class="nav-center">
            <a href="index.php" class="btn-back-home">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>

        <?php if ($user_id): ?>
        <a href="profil.php" class="btn-profile">
            <img src="<?= $foto_profil ?>" alt="Profil" class="navbar-profile-img" onerror="this.src='uploads/default.jpeg';">
            <span><?= $nama_tampil ?></span>
        </a>
        <?php else: ?>
        <a href="login.php" class="btn-profile">
            <span>Login</span>
        </a>
        <?php endif; ?>
    </div>
</nav>

<!-- LAYOUT UTAMA -->
<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-user">
            <div class="avatar-container">
                <img src="<?= $foto_profil ?>" alt="Foto Profil" onerror="this.src='uploads/default.jpeg';">
            </div>
            <strong><?= $nama_tampil ?></strong>
            <span><?= $role_user ?></span>
        </div>

        <ul class="menu-list">
            <li>
                <a href="profil.php">
                    <span class="menu-icon"><i class="fa-solid fa-house"></i></span>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="favorit.php">
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
                <a href="tentang.php" class="active">
                    <span class="menu-icon"><i class="fa-solid fa-circle-info"></i></span>
                    Tentang
                </a>
            </li>
        </ul>

        <div class="sidebar-decoration">
            <p>Temukan Kost Impianmu!</p>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">

        <!-- HERO BANNER -->
        <section class="welcome-banner">
            <div class="welcome-circle"></div>
            <div class="welcome-text">
                <h1>Tentang AKSARA KOST</h1>
                <p>Solusi terbaik untuk menemukan kost impianmu dengan mudah, aman, dan terpercaya.</p>
            </div>
            <div class="welcome-icon">
                <img src="uploads/logokost.png" alt="Logo Aksara Kost">
            </div>
        </section>

        <!-- DESKRIPSI -->
        <section class="content-card">
            <p>
                <strong>AKSARA KOST</strong> adalah platform pencarian kost online modern yang dirancang untuk memudahkan kamu menemukan kost terbaik dengan lokasi strategis, harga terjangkau, dan fasilitas lengkap. Kami hadir untuk memberikan pengalaman pencarian tempat tinggal yang nyaman, aman, dan terpercaya bagi para perantau, mahasiswa, maupun pekerja.
            </p>
        </section>

        <!-- KEUNGGULAN -->
        <section class="feature-wrapper">
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                <h3>Mudah Dicari</h3>
                <p>Pencarian kost jadi lebih cepat dan praktis sesuai lokasi serta kriteria pilihanmu.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Aman & Terpercaya</h3>
                <p>Data kost telah diverifikasi untuk memberikan keamanan dan kenyamanan bagi setiap pengguna.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
                <h3>Dukungan 24/7</h3>
                <p>Siap membantu kebutuhan informasi hunianmu kapan saja saat kamu membutuhkannya.</p>
            </div>
        </section>

        <!-- VISI DAN MISI -->
        <section class="visi-misi">
            <div class="vm-card">
                <div class="vm-header">
                    <i class="fa-solid fa-bullseye"></i>
                    <h3>Visi</h3>
                </div>
                <div class="vm-body">
                    <p>
                        Menjadi platform pencarian kost terdepan di Indonesia yang inovatif, terpercaya, dan memudahkan masyarakat dalam menemukan tempat tinggal impian dengan mudah.
                    </p>
                </div>
            </div>

            <div class="vm-card">
                <div class="vm-header">
                    <i class="fa-solid fa-list-check"></i>
                    <h3>Misi</h3>
                </div>
                <div class="vm-body">
                    <ul>
                        <li>Menyediakan informasi kost yang akurat dan terpercaya.</li>
                        <li>Memberikan pengalaman pencarian kost yang mudah dan nyaman.</li>
                        <li>Membantu pengguna menemukan hunian sesuai kebutuhan dan anggaran.</li>
                        <li>Terus berinovasi untuk meningkatkan kualitas pelayanan terbaik.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- FOOTER / INFO KECIL -->
        <footer class="footer-card">
            <div class="footer-logo-box">
                <img src="uploads/logokost.png" alt="Logo Aksara Kost">
                <div class="footer-brand-text">
                    <span class="footer-brand-title">Aksara</span>
                    <span class="footer-brand-subtitle">KOST</span>
                </div>
            </div>

            <div class="footer-info">
                <h4>Tentang Kami</h4>
                <p>AKSARA KOST © 2026. Solusi Kost Terbaik untuk Kamu ♡</p>
            </div>
        </footer>

    </main>

</div>

</body>
</html>