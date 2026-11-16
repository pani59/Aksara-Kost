<?php
session_start();
require_once __DIR__ . '/../database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| PROSES HAPUS PESANAN
|--------------------------------------------------------------------------
*/
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $booking_id = (int)$_GET['id'];
    if ($booking_id > 0) {
        mysqli_query($conn, "DELETE FROM review WHERE booking_id = '$booking_id'");
        $delete = mysqli_query($conn, "DELETE FROM booking WHERE booking_id = '$booking_id' AND user_id = '$user_id'");
        if ($delete) {
            echo "<script>alert('Pesanan berhasil dihapus!'); window.location.href='pesanan.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus pesanan.'); window.location.href='pesanan.php';</script>";
        }
        exit;
    }
}

$query = mysqli_query($conn, "SELECT * FROM user WHERE user_id = '$user_id'");
$user = mysqli_fetch_assoc($query);

// Cari berkas foto berdasarkan user_id di folder uploads
$foto_path = '../uploads/default.jpeg';
$ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
$has_foto = false;

foreach ($ekstensi_diizinkan as $ext) {
    $file_cek = '../uploads/user_' . $user_id . '.' . $ext;
    if (file_exists($file_cek)) {
        $foto_path = $file_cek;
        $has_foto = true;
        break;
    }
}

// Query mengambil seluruh data booking pengguna beserta statusnya
$q_pesanan = mysqli_query($conn, "
    SELECT b.booking_id, b.tanggal_survey, COALESCE(b.status_booking, 'Menunggu') AS status, k.nama_kos, k.alamat, km.harga, r.review_id, r.rating, r.komentar
    FROM booking b
    JOIN kamar km ON b.kamar_id = km.kamar_id
    JOIN kos k ON km.kos_id = k.kos_id
    LEFT JOIN review r ON b.booking_id = r.booking_id
    WHERE b.user_id = '$user_id' 
    ORDER BY b.booking_id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan Saya - KOST Finder</title>
    <link rel="icon" href="../uploads/logo finder.png" type="image/png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: #fff8fb; color: #333; }
        a { text-decoration: none; }
        .navbar { background: #ffffff; border-bottom: 1px solid #f0dce6; position: sticky; top: 0; z-index: 1000; }
        .navbar-container { max-width: 1200px; margin: auto; padding: 16px 25px; display: flex; align-items: center; justify-content: space-between; }
        .logo { color: #c72f78; font-size: 24px; font-weight: bold; display: inline-flex; align-items: center; gap: 10px; }
        .logo span { color: #333; }
        .nav-menu { display: flex; gap: 25px; align-items: center; }
        .nav-menu a { color: #555; font-size: 14px; }
        
        /* STYLE TOMBOL PROFIL USER BERFOTO (NAVBAR) */
        .btn-profile { 
            background: #fff0f7; 
            color: #c72f78 !important; 
            border: 1px solid #f8c5dc; 
            padding: 5px 14px 5px 8px; 
            border-radius: 20px; 
            font-weight: bold; 
            font-size: 13px; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
        }
        .btn-profile img {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            object-fit: cover;
        }

        .layout { max-width: 1100px; margin: 30px auto; display: grid; grid-template-columns: 250px 1fr; gap: 25px; padding: 0 20px; }
        .sidebar { background: #fff; padding: 25px 20px; border-radius: 15px; border: 1px solid #f0e1e8; height: fit-content; }
        .sidebar-user { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #f0e1e8; margin-bottom: 20px; }
        
        /* Style Foto Profil Lingkaran Sidebar */
        .avatar-img { 
            width: 75px; 
            height: 75px; 
            border-radius: 50%; 
            object-fit: cover; 
            margin: 0 auto 10px; 
            display: block; 
            border: 2px solid #c72f78; 
        }

        .sidebar-user strong { display: block; font-size: 15px; color: #333; }
        .sidebar-user span { font-size: 12px; color: #888; }
        .menu-list { list-style: none; }
        .menu-list a { display: flex; align-items: center; gap: 10px; padding: 12px 15px; color: #555; font-size: 14px; border-radius: 10px; margin-bottom: 6px; font-weight: 500; transition: 0.2s; }
        .menu-list a.active, .menu-list a:hover { background: #fff0f7; color: #c72f78; font-weight: bold; }
        .main-card { background: #fff; padding: 35px; border-radius: 15px; border: 1px solid #f0e1e8; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .card-header h2 { font-size: 20px; color: #333; }
        .booking-item { background: #fafafa; border: 1px solid #eee; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .booking-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .booking-title { font-size: 16px; font-weight: bold; color: #333; }
        .booking-sub { font-size: 13px; color: #777; margin-top: 2px; }
        .booking-price { font-size: 14px; font-weight: bold; color: #c72f78; margin-top: 5px; }
        
        /* Badge Status */
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; margin-top: 6px; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        /* Style Tombol Action */
        .action-group { display: flex; gap: 8px; align-items: center; justify-content: flex-end; }
        .btn-cetak { background: #0d6efd; color: #ffffff !important; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; transition: 0.2s; border: none; }
        .btn-cetak:hover { background: #0b5ed7; }
        .btn-cetak-disabled { background: #6c757d; color: #ffffff !important; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; opacity: 0.65; cursor: not-allowed; border: none; }
        .btn-hapus { background: #dc3545; color: #ffffff !important; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; display: inline-block; transition: 0.2s; }
        .btn-hapus:hover { background: #bb2d3b; }

        .form-review-inline { background: #fff0f7; border: 1px solid #f8c5dc; padding: 15px; border-radius: 10px; margin-top: 15px; }
        .form-review-inline label { font-size: 13px; font-weight: bold; color: #c72f78; display: block; margin-bottom: 6px; }
        .form-review-inline select, .form-review-inline textarea { width: 100%; padding: 8px; border: 1px solid #e4cbd7; border-radius: 6px; font-size: 13px; margin-bottom: 10px; outline: none; background: #fff; }
        .btn-submit-rev { background: #c72f78; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: bold; cursor: pointer; }
        .review-done { background: #e8f5e9; border: 1px solid #c8e6c9; padding: 12px; border-radius: 8px; font-size: 13px; color: #2e7d32; margin-top: 15px; }

        @media (max-width: 768px) { .layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="logo">
            <img src="../uploads/logo finder.png" alt="KOST Finder Logo" style="height: 35px; width: auto; vertical-align: middle; margin-right: 8px;">
            KOST <span>Finder</span>
        </a>
        <div class="nav-menu">
            <a href="index.php">Beranda</a>
            <a href="daftarkos.php">Daftar Kos</a>
            <a href="tentang.php">Tentang</a>
            <a href="profil.php" class="btn-profile">
                <?php if ($has_foto): ?>
                    <img src="<?= htmlspecialchars($foto_path); ?>" alt="Foto Profil">
                <?php else: ?>
                    👤
                <?php endif; ?>
                <span><?= htmlspecialchars($user['nama'] ?? $_SESSION['nama']); ?></span>
            </a>
        </div>
    </div>
</nav>

<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-user">
            <!-- Tampilkan foto profil dari folder uploads -->
            <img src="<?= htmlspecialchars($foto_path); ?>" alt="Foto Profil" class="avatar-img">
            <strong><?= htmlspecialchars($user['nama']); ?></strong>
            <span><?= htmlspecialchars($user['role'] ?? 'pengguna'); ?></span>
        </div>
        <ul class="menu-list">
            <li><a href="profil.php">🏠 Dashboard</a></li>
            <li><a href="favorit.php">🤍 Favorit</a></li>
            <li><a href="booking.php">📌 Booking</a></li>
            <li><a href="pesanan.php" class="active">📋 Pesanan</a></li>
            <li><a href="tentang.php">⚙️ Tentang</a></li>
        </ul>
    </aside>

    <main class="main-card">
        <div class="card-header">
            <h2>Riwayat Pesanan & Ulasan Kos</h2>
        </div>

        <?php if (mysqli_num_rows($q_pesanan) > 0): ?>
            <?php while ($b = mysqli_fetch_assoc($q_pesanan)): 
                $status_raw = strtolower($b['status']);
                $is_approved = ($status_raw === 'disetujui' || $status_raw === 'approved' || $status_raw === 'selesai');
            ?>
                <div class="booking-item">
                    <div class="booking-header">
                        <div>
                            <div class="booking-title"><?= htmlspecialchars($b['nama_kos']); ?></div>
                            <div class="booking-sub">📍 <?= htmlspecialchars($b['alamat']); ?></div>
                            <div class="booking-price">Rp <?= number_format($b['harga'] ?? 0, 0, ',', '.'); ?>/bulan</div>
                            
                            <!-- Display Status Badge -->
                            <?php if ($is_approved): ?>
                                <span class="badge badge-success">✓ Disetujui</span>
                            <?php elseif ($status_raw === 'ditolak'): ?>
                                <span class="badge badge-danger">✕ Ditolak</span>
                            <?php else: ?>
                                <span class="badge badge-warning">⏳ Menunggu Konfirmasi</span>
                            <?php endif; ?>
                        </div>

                        <div class="action-group">
                            <!-- Tombol Cetak Bukti -->
                            <?php if ($is_approved): ?>
                                <a href="cetak_booking.php?id=<?= $b['booking_id']; ?>" 
                                   target="_blank" 
                                   class="btn-cetak">
                                   🖨️ Cetak Bukti
                                </a>
                            <?php else: ?>
                                <button class="btn-cetak-disabled" disabled title="Cetak bukti hanya dapat dilakukan jika booking telah disetujui">
                                   🖨️ Cetak Bukti
                                </button>
                            <?php endif; ?>

                            <!-- Tombol Hapus Pesanan -->
                            <a href="pesanan.php?action=hapus&id=<?= $b['booking_id']; ?>" 
                               onclick="return confirm('Apakah Anda yakin ingin menghapus pesanan ini?');" 
                               class="btn-hapus">
                               🗑️ Hapus
                            </a>
                        </div>
                    </div>

                    <!-- Form / Hasil Ulasan (Hanya tampil jika status Disetujui / Approved) -->
                    <?php if ($is_approved): ?>
                        <?php if (empty($b['review_id'])): ?>
                            <form action="proses_review.php" method="POST" class="form-review-inline">
                                <input type="hidden" name="booking_id" value="<?= $b['booking_id']; ?>">
                                
                                <label>Beri Ulasan & Rating Kos Ini:</label>
                                <select name="rating" required>
                                    <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Bagus)</option>
                                    <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
                                    <option value="3">⭐⭐⭐ (3 - Cukup)</option>
                                    <option value="2">⭐⭐ (2 - Buruk)</option>
                                    <option value="1">⭐ (1 - Sangat Buruk)</option>
                                </select>

                                <textarea name="komentar" rows="2" placeholder="Bagaimana pengalaman kamu ngekos di sini?" required></textarea>
                                
                                <button type="submit" name="submit_review" class="btn-submit-rev">Kirim Ulasan</button>
                            </form>
                        <?php else: ?>
                            <div class="review-done">
                                <strong>✔ Ulasan Terkirim:</strong>
                                <div style="color:#f59e0b; margin:2px 0;"><?= str_repeat('★', (int)$b['rating']); ?> (<?= $b['rating']; ?>/5)</div>
                                <em>"<?= htmlspecialchars($b['komentar']); ?>"</em>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="font-size: 13px; color: #888;">Belum ada pesanan kos.</p>
        <?php endif; ?>
    </main>
</div>

</body>
</html>