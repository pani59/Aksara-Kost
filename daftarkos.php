<?php
session_start();
require_once __DIR__ . '/database.php';

// Menyesuaikan koneksi database
if (!isset($db) && isset($conn)) {
    $db = $conn;
} elseif (!isset($db) && isset($koneksi)) {
    $db = $koneksi;
}

if (!isset($db) || mysqli_connect_errno()) {
    die("Koneksi database gagal. Periksa kembali file database.php.");
}

$filter_tipe = $_GET['tipe'] ?? '';
$cari        = trim($_GET['cari'] ?? '');

$sql = "
    SELECT k.*, 
    (
        SELECT fk.path_foto 
        FROM foto_kos fk 
        WHERE fk.kos_id = k.kos_id 
        ORDER BY fk.foto_id ASC LIMIT 1
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

$where = [];
if ($filter_tipe !== '') {
    $where[] = "k.tipe_kos = '" . mysqli_real_escape_string($db, $filter_tipe) . "'";
}
if ($cari !== '') {
    $where[] = "(k.nama_kos LIKE '%" . mysqli_real_escape_string($db, $cari) . "%' OR k.alamat LIKE '%" . mysqli_real_escape_string($db, $cari) . "%')";
}
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY k.kos_id DESC";

$result = mysqli_query($db, $sql);

function rupiah($angka) {
    return ($angka > 0) ? 'Rp ' . number_format((int)$angka, 0, ',', '.') : 'Harga belum tersedia';
}
$default_image = 'assets/images/default-kos.jpg';

// Data pengguna untuk navbar jika login
$user_id = $_SESSION['user_id'] ?? null;
$user = [];
if ($user_id) {
    $q_user = mysqli_query($db, "SELECT * FROM user WHERE user_id = '$user_id'");
    if ($q_user) {
        $user = mysqli_fetch_assoc($q_user);
    }
}

$foto_session = (!empty($_SESSION['foto']) && file_exists('uploads/' . $_SESSION['foto']))
    ? 'uploads/' . $_SESSION['foto'] . '?v=' . time()
    : 'uploads/default.jpeg';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kos - AKSARA KOST</title>
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
        .nav-menu a { color: var(--muted); font-size: 14px; font-weight: 600; transition: 0.3s; }
        .nav-menu a:hover, .nav-menu a.active-link { color: var(--sage-dark); font-weight: 700; }

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
            LAYOUT UTAMA
        ================================= */
        .container { max-width: 1300px; margin: 35px auto; padding: 0 25px; }

        /* WELCOME BANNER */
        .welcome-banner {
            min-height: 150px; padding: 30px 38px; border-radius: 22px;
            background: linear-gradient(120deg, #D1E0D1, #EBF2EB);
            border: 1px solid var(--sage-border); display: flex; align-items: center;
            justify-content: space-between; overflow: hidden; position: relative; margin-bottom: 25px;
        }
        .welcome-text h1 { font-size: 28px; color: var(--text); margin-bottom: 6px; font-weight: 800; }
        .welcome-text p { color: var(--muted); font-size: 14px; }
        .welcome-circle { position: absolute; width: 220px; height: 220px; background: rgba(255,255,255,0.3); border-radius: 50%; right: 80px; top: 20px; }

        /* FILTER BAR */
        .filter-header { 
            background: #fff; 
            padding: 20px 25px; 
            border-radius: 22px; 
            border: 1px solid var(--sage-border); 
            display: flex; 
            gap: 15px; 
            flex-wrap: wrap; 
            align-items: center; 
            margin-bottom: 30px; 
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }
        .filter-header input, .filter-header select { 
            padding: 12px 18px; 
            border: 1px solid var(--sage-border); 
            border-radius: 14px; 
            outline: none; 
            font-size: 13px; 
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            background: #ffffff;
            transition: 0.3s;
        }
        .filter-header input:focus, .filter-header select:focus {
            border-color: var(--sage);
            box-shadow: 0 0 0 3px rgba(135,169,135,0.12);
        }
        .filter-header input[type="text"] { flex: 1; min-width: 220px; }
        .filter-header button { 
            background: var(--sage); 
            color: #fff; 
            border: none; 
            padding: 12px 24px; 
            border-radius: 30px; 
            font-size: 13px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: 0.3s;
            box-shadow: 0 4px 14px rgba(135,169,135,0.2);
        }
        .filter-header button:hover { background: var(--sage-dark); transform: translateY(-1px); }

        /* GRID DAFTAR KOS HORIZONTAL (2 Kolom seragam) */
        .kos-grid-2col { display: grid; grid-template-columns: repeat(2, 1fr); gap: 22px; }
        .kos-card-horizontal { 
            background: #fff; 
            border-radius: 22px; 
            border: 1px solid var(--sage-border); 
            padding: 20px; 
            display: flex; 
            gap: 20px; 
            align-items: center;
            transition: 0.3s;
            box-shadow: 0 5px 20px rgba(135,169,135,0.06);
        }
        .kos-card-horizontal:hover { transform: translateY(-3px); border-color: var(--sage); box-shadow: 0 8px 22px rgba(135,169,135,0.12); }

        .kos-card-horizontal img { width: 160px; height: 130px; object-fit: cover; border-radius: 16px; background: var(--sage-soft); }
        .kos-info-box { flex: 1; display: flex; flex-direction: column; justify-content: space-between; min-width: 0; }
        .kos-info-box h3 { font-size: 17px; color: var(--text); font-weight: 800; margin-bottom: 6px; }
        .kos-info-box .lokasi { font-size: 12px; color: var(--muted); margin-bottom: 6px; line-height: 1.4; }
        .kos-info-box .lokasi i { color: var(--sage); }
        .kos-info-box .harga { font-size: 15px; font-weight: 800; color: var(--sage-dark); margin-bottom: 4px; }
        .kos-info-box .rating { font-size: 12px; color: #f59e0b; font-weight: 700; margin-bottom: 10px; }
        
        .btn-detail { 
            align-self: flex-start; 
            background: var(--sage-soft); 
            color: var(--sage-dark); 
            border: 1px solid var(--sage-border); 
            padding: 8px 18px; 
            border-radius: 30px; 
            font-size: 12px; 
            font-weight: 700; 
            transition: 0.3s; 
        }
        .btn-detail:hover { background: var(--sage); color: #fff; }

        @media (max-width: 900px) {
            .kos-grid-2col { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .navbar-container { padding: 12px 18px; flex-wrap: wrap; gap: 10px; justify-content: space-between; }
            .nav-menu { display: none; }
            .container { padding: 0 15px; margin: 20px auto; }
            .welcome-banner { padding: 25px 20px; }
            .welcome-text h1 { font-size: 22px; }
            .kos-card-horizontal { flex-direction: column; align-items: stretch; }
            .kos-card-horizontal img { width: 100%; height: 160px; }
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
            <a href="daftarkos.php" class="active-link">Kost</a>
            <a href="index.php#tentang">Tentang</a>
        </div>

        <div>
            <?php if ($user_id): ?>
                <a href="profil.php" class="btn-profile">
                    <img src="<?= htmlspecialchars($foto_session); ?>" alt="Profil" class="navbar-profile-img" onerror="this.src='uploads/default.jpeg';">
                    <span><?= htmlspecialchars($user['nama'] ?? $_SESSION['nama'] ?? 'User'); ?></span>
                </a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Masuk</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- CONTAINER UTAMA -->
<div class="container">

    <!-- WELCOME BANNER -->
    <section class="welcome-banner">
        <div class="welcome-circle"></div>
        <div class="welcome-text">
            <h1>Daftar Seluruh Kost</h1>
            <p>Temukan berbagai pilihan kost terbaik yang sesuai dengan kebutuhan dan anggaranmu.</p>
        </div>
    </section>

    <!-- FILTER BAR -->
    <form action="" method="GET" class="filter-header">
        <input type="text" name="cari" placeholder="Cari nama atau lokasi kos..." value="<?= htmlspecialchars($cari); ?>">
        <select name="tipe">
            <option value="">Semua Tipe Kost</option>
            <option value="Putra" <?= $filter_tipe === 'Putra' ? 'selected' : ''; ?>>Putra</option>
            <option value="Putri" <?= $filter_tipe === 'Putri' ? 'selected' : ''; ?>>Putri</option>
            <option value="Campur" <?= $filter_tipe === 'Campur' ? 'selected' : ''; ?>>Campur</option>
        </select>
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari Kost</button>
    </form>

    <!-- GRID KOS -->
    <div class="kos-grid-2col">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($k = mysqli_fetch_assoc($result)): ?>
                <?php 
                    $foto = !empty($k['foto_kos']) ? 'uploads/'.$k['foto_kos'] : $default_image; 
                    $rating_val = !empty($k['avg_rating']) ? $k['avg_rating'] : 4.5;
                ?>
                <div class="kos-card-horizontal">
                    <img src="<?= htmlspecialchars($foto); ?>" alt="<?= htmlspecialchars($k['nama_kos']); ?>" onerror="this.src='<?= $default_image; ?>';">
                    <div class="kos-info-box">
                        <div>
                            <h3><?= htmlspecialchars($k['nama_kos']); ?></h3>
                            <p class="lokasi"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($k['alamat']); ?></p>
                            <p class="harga"><?= rupiah($k['harga_mulai']); ?><small style="font-weight:normal; color:var(--muted); font-size:11px;">/bulan</small></p>
                            <p class="rating">★ <?= number_format((float)$rating_val, 1); ?></p>
                        </div>
                        <a href="detail.php?id=<?= $k['kos_id']; ?>" class="btn-detail">
                            <i class="fa-solid fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; background:#fff; border:1px solid var(--sage-border); border-radius:22px; text-align:center; padding:50px 20px;">
                <div style="font-size:50px; color:var(--sage); margin-bottom:12px;"><i class="fa-regular fa-folder-open"></i></div>
                <h3 style="color:var(--text); font-size:18px; margin-bottom:6px; font-weight:800;">Kost Tidak Ditemukan</h3>
                <p style="color:var(--muted); font-size:13px;">Maaf, kost yang kamu cari tidak tersedia atau kata kunci kurang tepat.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>