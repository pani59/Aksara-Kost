<?php
// Aktifkan error reporting untuk mendeteksi pesan kesalahan jika ada
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/database.php';

// Menyesuaikan koneksi database ($conn atau $koneksi)
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

if (!isset($conn) || !$conn) {
    die("Koneksi database gagal. Periksa database.php");
}

$error = '';
$success = '';

// ========================================
// PROSES REGISTER
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $no_wa    = trim($_POST['no_wa'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = trim($_POST['role'] ?? '');

    if ($nama === '' || $email === '' || $password === '' || $role === '') {
        $error = "Harap isi semua kolom wajib dan pilih role!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal harus 6 karakter!";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt_cek = mysqli_prepare($conn, "SELECT email FROM user WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_cek, "s", $email);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Email sudah terdaftar! Gunakan email lain.";
            mysqli_stmt_close($stmt_cek);
        } else {
            mysqli_stmt_close($stmt_cek);

            // LOGIKA STATUS VERIFIKASI BERDASARKAN ROLE SESUAI ENUM DATABASE
            if ($role === 'User') {
                $status_verifikasi = 'Terverifikasi'; // User langsung terverifikasi
            } else {
                $status_verifikasi = 'Menunggu';      // Owner menunggu konfirmasi admin
            }

            // Query insert database dengan variabel status_verifikasi tersebut
            $query_insert = "INSERT INTO user (nama, email, password, role, no_wa, status_verifikasi) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $query_insert);

            if ($stmt_insert) {
                mysqli_stmt_bind_param($stmt_insert, "ssssss", $nama, $email, $password, $role, $no_wa, $status_verifikasi);
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    // AMBIL USER_ID UNIK YANG BARU SAJA MASUK KE DATABASE
                    $user_id = mysqli_insert_id($conn);

                    // LOGIKA PENYIMPANAN FOTO DENGAN ID UNIK DI FOLDER UPLOADS
                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                        $file_tmp   = $_FILES['foto']['tmp_name'];
                        $file_ext   = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                        if (in_array($file_ext, $allowed_ext, true)) {
                            $nama_foto = 'user_' . $user_id . '.' . $file_ext;
                            $target_dir = __DIR__ . '/uploads/';

                            if (!is_dir($target_dir)) {
                                mkdir($target_dir, 0777, true);
                            }

                            move_uploaded_file($file_tmp, $target_dir . $nama_foto);
                        }
                    }

                    // AUTO-LOGIN KHUSUS USER (LANGSUNG KE INDEX.PHP TANPA LOGIN LAGI)
                    if ($role === 'User') {
                        $_SESSION['is_login'] = true;
                        $_SESSION['user_id']  = $user_id;
                        $_SESSION['nama']     = $nama;
                        $_SESSION['role']     = 'User';

                        mysqli_stmt_close($stmt_insert);
                        header("Location: index.php");
                        exit;
                    } else {
                        // Jika Owner, tampilkan pesan menunggu konfirmasi seperti biasa
                        $success = "Pendaftaran berhasil! Akun Anda sedang <b>Menunggu Konfirmasi</b> dari Admin. Silakan tunggu hingga disetujui untuk dapat login.";
                    }
                } else {
                    $error = "Gagal mendaftar: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt_insert);
            } else {
                $error = "Terjadi kesalahan pada sistem database.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - AKSARA KOST</title>
    <link rel="icon" href="uploads/logokost.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; background: #FAF9F6; color: #2C3E35; min-height: 100vh; }

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

        .navbar { background: rgba(250,249,246,0.96); backdrop-filter: blur(15px); border-bottom: 1px solid var(--sage-border); position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .navbar-container { width: 100%; max-width: 1300px; margin: auto; padding: 14px 25px; display: flex; align-items: center; justify-content: space-between; }
        
        .logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon { width: 34px; height: 34px; object-fit: contain; }
        .logo-text-group { display: flex; flex-direction: column; line-height: 1.1; }
        .logo-title { color: var(--sage-dark); font-size: 20px; font-weight: 800; letter-spacing: -0.5px; }
        .logo-subtitle { color: var(--sage-dark); font-size: 14px; font-weight: 700; }
        
        .nav-menu { display: flex; gap: 35px; align-items: center; position: absolute; left: 50%; transform: translateX(-50%); }
        .nav-menu a.nav-item { color: var(--muted); font-size: 15px; font-weight: 800; text-decoration: none; transition: color 0.2s; }
        .nav-menu a.nav-item:hover { color: var(--sage-dark) !important; }
        .nav-spacer { width: 100px; }

        .register-page { width: 100%; padding: 35px 25px 50px; }
        .register-wrapper {
            max-width: 1150px; width: 100%; margin: auto; display: grid;
            grid-template-columns: 0.95fr 1.05fr; background: #ffffff;
            border-radius: 22px; overflow: hidden; border: 1px solid var(--sage-border);
            box-shadow: 0 15px 45px rgba(135,169,135,0.1);
        }

        .register-banner {
            background: linear-gradient(145deg, var(--sage-soft), #D1E0D1);
            padding: 48px 42px; display: flex; flex-direction: column;
            justify-content: center; position: relative; overflow: hidden;
        }
        .register-banner-content { position: relative; z-index: 2; }
        .banner-title { font-size: 35px; line-height: 1.25; font-weight: 800; color: var(--text); margin-bottom: 12px; }
        .banner-title span { color: var(--sage-dark); }
        .banner-description { color: var(--muted); font-size: 13px; line-height: 1.8; margin-bottom: 28px; }

        .banner-features { display: flex; flex-direction: column; gap: 14px; }
        .feature { display: flex; align-items: center; gap: 12px; color: var(--text); font-size: 13px; font-weight: 600; }
        .feature-icon {
            width: 35px; height: 35px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: var(--sage); color: #ffffff; font-size: 14px;
        }

        .register-form-container { padding: 45px 55px; display: flex; flex-direction: column; justify-content: center; }

        .form-logo { display: flex; justify-content: center; margin-bottom: 16px; }
        .form-logo-icon { width: 62px; height: 62px; display: flex; align-items: center; justify-content: center; }
        .form-logo-icon img { width: 100%; height: 100%; object-fit: contain; }

        .form-title { text-align: center; font-size: 30px; font-weight: 800; color: var(--text); margin-bottom: 7px; }
        .form-title span { color: var(--sage-dark); }
        .form-subtitle { text-align: center; font-size: 13px; color: var(--muted); margin-bottom: 25px; }

        .alert-error {
            background: #fff0f0; color: #d63031; border: 1px solid #ffcaca;
            border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; font-size: 12px;
        }
        .alert-success {
            background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9;
            border-radius: 10px; padding: 16px; margin-bottom: 18px; font-size: 13px; text-align: center; line-height: 1.6;
        }

        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; color: var(--text); font-size: 13px; font-weight: 700; margin-bottom: 8px; }
        .input-wrapper { position: relative; }
        .input-wrapper > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--sage); font-size: 14px; }

        .form-group input, .form-group select {
            width: 100%; height: 49px; padding: 0 43px;
            border: 1px solid var(--sage-border); border-radius: 10px;
            background: #ffffff; color: var(--text); outline: none; font-family: inherit; font-size: 12px; transition: 0.3s;
        }
        .form-group select { cursor: pointer; }
        .form-group input[type="file"] { padding: 12px 15px; height: auto; background: #fafafa; cursor: pointer; }
        .form-group input:focus, .form-group select:focus { border-color: var(--sage); box-shadow: 0 0 0 3px rgba(135,169,135,0.12); }

        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--muted); font-size: 15px; cursor: pointer; }

        .btn-submit {
            width: 100%; height: 51px; border: none; border-radius: 30px;
            background: var(--sage); color: #ffffff;
            font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: 0.3s;
            margin-top: 5px; box-shadow: 0 6px 16px rgba(135,169,135,0.2);
        }
        .btn-submit:hover { background: var(--sage-dark); transform: translateY(-2px); }

        .or-divider { display: flex; align-items: center; gap: 12px; margin: 19px 0; color: var(--muted); font-size: 12px; }
        .or-divider::before, .or-divider::after { content: ""; height: 1px; flex: 1; background: var(--sage-border); }

        .btn-login-link {
            width: 100%; height: 49px; display: flex; align-items: center; justify-content: center; gap: 9px;
            border: 1.5px solid var(--sage); border-radius: 30px; text-decoration: none; color: var(--sage-dark); font-size: 13px; font-weight: 700; transition: 0.3s;
        }
        .btn-login-link:hover { background: var(--sage-soft); }

        @media(max-width: 950px) {
            .nav-menu { position: static; transform: none; }
            .nav-spacer { display: none; }
            .register-wrapper { grid-template-columns: 0.9fr 1.1fr; }
            .register-banner { padding: 30px; }
            .register-form-container { padding: 35px 30px; }
        }
        @media(max-width: 650px) {
            .navbar-container { padding: 12px 15px; flex-wrap: wrap; gap: 12px; justify-content: space-between; }
            .nav-menu { width: 100%; justify-content: center; gap: 20px; }
            .register-page { padding: 20px 12px 30px; }
            .register-wrapper { display: flex; flex-direction: column; }
            .register-form-container { padding: 30px 22px; }
        }
    </style>
</head>
<body>

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
            <a href="index.php" class="nav-item">Beranda</a>
            <a href="index.php#daftar-kos" class="nav-item">Kost</a>
            <a href="index.php#peta-lokasi" class="nav-item">Peta</a>
            <a href="index.php#tentang" class="nav-item">Tentang</a>
        </div>

        <div class="nav-spacer"></div>
    </div>
</nav>

<main class="register-page">
    <div class="register-wrapper">

        <!-- KIRI: BANNER -->
        <section class="register-banner">
            <div class="register-banner-content">
                <h1 class="banner-title">
                    Bergabung dengan <span>AKSARA KOST</span>
                </h1>
                <p class="banner-description">
                    Daftar sebagai Owner atau User. Setiap pendaftaran akun baru memerlukan verifikasi dan konfirmasi dari Admin sistem.
                </p>
                <div class="banner-features">
                    <div class="feature">
                        <div class="feature-icon"><i class="fa-solid fa-user-shield"></i></div>
                        <span>Verifikasi Oleh Admin</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fa-solid fa-house"></i></div>
                        <span>Kelola & Cari Kost Mudah</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- KANAN: FORM -->
        <section class="register-form-container">
            <div class="form-logo">
                <div class="form-logo-icon">
                    <img src="uploads/logokost.png" alt="Logo Aksara Kost">
                </div>
            </div>

            <h1 class="form-title">Daftar <span>Akun Baru</span></h1>
            <p class="form-subtitle">Buat akun baru untuk mulai bergabung</p>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check" style="font-size: 18px; margin-bottom: 6px; display: block;"></i>
                    <?= $success; ?>
                    <div style="margin-top: 15px;">
                        <a href="login.php" class="btn-login-link" style="background: var(--sage); color: white; border: none;">
                            <i class="fa-solid fa-right-to-bracket"></i> Kembali ke Halaman Login
                        </a>
                    </div>
                </div>
            <?php else: ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <!-- NAMA -->
                <div class="form-group">
                    <label>Nama</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="nama" placeholder="Masukkan nama Anda" autocomplete="name" required>
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="Masukkan email aktif" autocomplete="email" required>
                    </div>
                </div>

                <!-- NOMOR WHATSAPP -->
                <div class="form-group">
                    <label>No WhatsApp</label>
                    <div class="input-wrapper">
                        <i class="fa-brands fa-whatsapp"></i>
                        <input type="text" name="no_wa" placeholder="Masukkan nomor WhatsApp">
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label>Password (Minimal 6 Karakter)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Masukkan password" minlength="6" autocomplete="new-password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Tampilkan password">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- ROLE -->
                <div class="form-group">
                    <label>Role</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-users"></i>
                        <select name="role" required>
                            <option value="" disabled selected hidden>Pilih Role</option>
                            <option value="Owner">Owner</option>
                            <option value="User">User</option>
                        </select>
                    </div>
                </div>

                <!-- FOTO PROFIL (DIKEMBALIKAN) -->
                <div class="form-group">
                    <label>Foto Profil (Opsional)</label>
                    <div class="input-wrapper">
                        <input type="file" name="foto" accept="image/*">
                    </div>
                </div>

                <!-- TOMBOL DAFTAR -->
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-plus"></i> &nbsp; Daftar Akun Baru
                </button>
            </form>

            <div class="or-divider">atau</div>

            <a href="login.php" class="btn-login-link">
                <i class="fa-solid fa-right-to-bracket"></i> Sudah punya akun? Login di sini
            </a>

            <?php endif; ?>
        </section>

    </div>
</main>

<script>
function togglePassword() {
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    if (password.type === 'password') {
        password.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>