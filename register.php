<?php
session_start();
require_once __DIR__ . '/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $no_wa    = mysqli_real_escape_string($conn, $_POST['no_wa']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (!empty($nama) && !empty($email) && !empty($password)) {
        // Validasi panjang password minimal 6 karakter
        if (strlen($password) < 6) {
            $error = "Password minimal harus 6 karakter!";
        } else {
            $check_email = mysqli_query($conn, "SELECT email FROM user WHERE email = '$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email sudah terdaftar!";
            } else {
                $query = "INSERT INTO user (nama, email, password, role, no_wa, status_verifikasi) 
                          VALUES ('$nama', '$email', '$password', 'User', '$no_wa', 'Terverifikasi')";

                if (mysqli_query($conn, $query)) {
                    $user_id = mysqli_insert_id($conn);
                    $nama_foto = 'default.jpeg';

                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                        $file_tmp  = $_FILES['foto']['tmp_name'];
                        $file_ext  = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

                        if (in_array($file_ext, $allowed_ext)) {
                            $nama_foto = 'user_' . $user_id . '.' . $file_ext;
                            move_uploaded_file($file_tmp, 'uploads/' . $nama_foto);
                        }
                    }

                    $_SESSION['user_id']  = $user_id;
                    $_SESSION['nama']     = $nama;
                    $_SESSION['role']     = 'User';
                    $_SESSION['foto']     = $nama_foto;
                    $_SESSION['is_login'] = true;

                    header("Location: profil.php");
                    exit;
                } else {
                    $error = "Gagal mendaftar: " . mysqli_error($conn);
                }
            }
        }
    } else {
        $error = "Harap isi semua kolom yang wajib!";
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
        /* =================================
            RESET DAN DASAR (SAGE GREEN THEME)
        ================================= */
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
        
        .nav-menu { display: flex; gap: 35px; align-items: center; position: absolute; left: 50%; transform: translateX(-50%); }
        .nav-menu a.nav-item { color: var(--muted); font-size: 15px; font-weight: 800; text-decoration: none; transition: color 0.2s; }
        .nav-menu a.nav-item:hover, .nav-menu a.nav-item.active-link { color: var(--sage-dark) !important; }

        .nav-spacer { width: 100px; }

        /* =================================
            AREA REGISTER
        ================================= */
        .register-page { width: 100%; padding: 35px 25px 50px; }
        .register-wrapper {
            max-width: 1150px;
            width: 100%;
            margin: auto;
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            background: #ffffff;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid var(--sage-border);
            box-shadow: 0 15px 45px rgba(135,169,135,0.1);
        }

        /* =================================
            BAGIAN KIRI (BANNER)
        ================================= */
        .register-banner {
            background: linear-gradient(145deg, var(--sage-soft), #D1E0D1);
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .register-banner::before {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            background: rgba(255,255,255,0.4);
            border-radius: 50%;
            top: -100px;
            left: -100px;
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

        /* =================================
            BAGIAN KANAN (FORM REGISTER)
        ================================= */
        .register-form-container { padding: 45px 55px; display: flex; flex-direction: column; justify-content: center; }

        .form-logo { display: flex; justify-content: center; margin-bottom: 16px; }
        .form-logo-icon {
            width: 62px; 
            height: 62px;
            background: transparent;
            border: none;
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        .form-logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .form-title { text-align: center; font-size: 30px; font-weight: 800; color: var(--text); margin-bottom: 7px; }
        .form-title span { color: var(--sage-dark); }
        .form-subtitle { text-align: center; font-size: 13px; color: var(--muted); margin-bottom: 25px; }

        .alert-error {
            background: #fff0f0; color: #d63031; border: 1px solid #ffcaca;
            border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; font-size: 12px;
        }
        .alert-error i { margin-right: 6px; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; color: var(--text); font-size: 13px; font-weight: 700; margin-bottom: 6px; }
        .input-wrapper { position: relative; }
        .input-wrapper > i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--sage); font-size: 14px; }

        .form-group input {
            width: 100%; height: 46px; padding: 0 15px 0 43px;
            border: 1px solid var(--sage-border); border-radius: 10px;
            background: #ffffff; color: var(--text); outline: none; font-family: inherit; font-size: 12px; transition: 0.3s;
        }
        .form-group input[type="file"] {
            padding: 10px 15px;
            height: auto;
            background: #fafafa;
            cursor: pointer;
        }
        .form-group input::placeholder { color: #a2a4b1; }
        .form-group input:focus { border-color: var(--sage); box-shadow: 0 0 0 3px rgba(135,169,135,0.12); }

        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--muted); font-size: 15px; cursor: pointer; }

        .btn-submit {
            width: 100%; height: 49px; border: none; border-radius: 30px;
            background: var(--sage); color: #ffffff;
            font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer; transition: 0.3s;
            margin-top: 8px; box-shadow: 0 6px 16px rgba(135,169,135,0.2);
        }
        .btn-submit:hover { background: var(--sage-dark); transform: translateY(-2px); }

        .footer-link { margin-top: 20px; text-align: center; color: var(--muted); font-size: 12px; }
        .footer-link a { color: var(--sage-dark); font-weight: 700; text-decoration: none; }

        /* =================================
            RESPONSIVE DESIGN
        ================================= */
        @media(max-width: 950px) {
            .nav-menu { position: static; transform: none; }
            .nav-spacer { display: none; }
            .register-wrapper { grid-template-columns: 0.9fr 1.1fr; }
            .register-banner { padding: 30px; }
            .register-form-container { padding: 35px 30px; }
            .banner-title { font-size: 29px; }
        }

        @media(max-width: 650px) {
            .navbar-container { padding: 12px 15px; flex-wrap: wrap; gap: 12px; justify-content: space-between; }
            .nav-menu { width: 100%; justify-content: center; gap: 20px; }
            .register-page { padding: 20px 12px 30px; }
            .register-wrapper { display: flex; flex-direction: column; border-radius: 17px; }
            .register-banner { padding: 27px 22px; }
            .banner-title { font-size: 28px; }
            .register-form-container { padding: 30px 22px; }
            .form-title { font-size: 25px; }
        }
    </style>
</head>
<body>

<!-- =================================
    NAVBAR
================================= -->
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

<!-- =================================
    HALAMAN REGISTER
================================= -->
<main class="register-page">
    <div class="register-wrapper">

        <!-- BAGIAN KIRI: BANNER -->
        <section class="register-banner">
            <div class="register-banner-content">
                <h1 class="banner-title">
                    Bergabung dengan 
                    <span>AKSARA KOST</span>
                </h1>

                <p class="banner-description">
                    Daftarkan akunmu dan temukan hunian kost impian 
                    dengan mudah, cepat, dan aman bersama kami.
                </p>

                <!-- KEUNGGULAN -->
                <div class="banner-features">
                    <div class="feature">
                        <div class="feature-icon"><i class="fa-solid fa-house"></i></div>
                        <span>Banyak Pilihan Kost</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <span>Aman & Terpercaya</span>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <span>Lokasi Strategis</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- BAGIAN KANAN: FORM PENDAFTARAN -->
        <section class="register-form-container">
            <div class="form-logo">
                <div class="form-logo-icon">
                    <img src="uploads/logokost.png" alt="Logo Aksara Kost">
                </div>
            </div>

            <h1 class="form-title">
                Daftar <span>Akun Baru</span>
            </h1>

            <p class="form-subtitle">
                Buat akun baru untuk mulai mencari kost
            </p>

            <!-- PESAN ERROR -->
            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- FORM REGISTER -->
            <form action="" method="POST" enctype="multipart/form-data">
                <!-- NAMA LENGKAP -->
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="Masukkan email" required>
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
                        <input type="password" name="password" id="password" placeholder="Masukkan password" minlength="6" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Tampilkan password">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- FOTO PROFIL -->
                <div class="form-group">
                    <label>Foto Profil (Opsional)</label>
                    <div class="input-wrapper">
                        <input type="file" name="foto" accept="image/*">
                    </div>
                </div>

                <!-- TOMBOL DAFTAR -->
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-plus"></i> &nbsp; Daftar Sekarang
                </button>
            </form>

            <div class="footer-link">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </section>

    </div>
</main>

<!-- =================================
    JAVASCRIPT PASSWORD TOGGLE
================================= -->
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