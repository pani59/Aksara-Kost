<?php
session_start();
require_once __DIR__ . '/../database.php';

// KONEKSI DATABASE
if (!isset($conn) && isset($koneksi)) {
    $conn = $koneksi;
}

if (!isset($conn) || !$conn) {
    die("Koneksi database gagal. Periksa database.php");
}

$error = '';
$username_input = '';
$role_input = '';

// ========================================
// PROSES LOGIN
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');

    $username_input = $username;
    $role_input = $role;

    if ($username === '' || $password === '' || $role === '') {
        $error = "Harap isi semua kolom!";
    } else {
        $query = "SELECT * FROM user WHERE nama = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);

        if (!$stmt) {
            $error = "Terjadi kesalahan pada database.";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);

                $db_password = $user['password'] ?? '';
                $db_role = strtolower(trim($user['role'] ?? ''));
                $input_role = strtolower(trim($role));

                // Memeriksa password biasa atau password hash
                $password_info = password_get_info($db_password);

                if (($password_info['algoName'] ?? 'unknown') !== 'unknown') {
                    $password_valid = password_verify($password, $db_password);
                } else {
                    $password_valid = hash_equals($db_password, $password);
                }

                // FLEKSIBEL: Jika akun di database adalah admin, izinkan masuk walau role pilihan berbeda (atau sesuaikan)
                $is_admin_account = ($db_role === 'admin');

                if (!$is_admin_account && $db_role !== $input_role) {
                    $error = "Role yang dipilih tidak sesuai dengan akun Anda!";
                } elseif (!$password_valid) {
                    $error = "Password salah!";
                } else {
                    // LOGIN BERHASIL
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['role'] = $is_admin_account ? $input_role : $user['role'];

                    // MENCARI FOTO PROFIL
                    $found_foto = 'default.jpeg';
                    $extensions = ['jpg', 'jpeg', 'png', 'gif'];

                    foreach ($extensions as $ext) {
                        $filename = 'user_' . $user['user_id'] . '.' . $ext;
                        if (file_exists(__DIR__ . '/../uploads/' . $filename)) {
                            $found_foto = $filename;
                            break;
                        }
                    }

                    $_SESSION['foto'] = $found_foto;
                    $_SESSION['is_login'] = true;

                    mysqli_stmt_close($stmt);

                    // PENGALIHAN BERDASARKAN ROLE YANG DIPILIH / AKTIF
                    $active_role = strtolower(trim($_SESSION['role']));
                    if ($active_role === 'admin') {
                        header("Location: ../admin/index.php");
                        exit;
                    } elseif ($active_role === 'owner') {
                        header("Location: ../owner/index2.php");
                        exit;
                    } else {
                        header("Location: index.php");
                        exit;
                    }
                }
            } else {
                $error = "Username tidak ditemukan!";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AKSARA KOST</title>
    <link rel="icon" href="../uploads/logokost.png" type="image/png">
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
           AREA LOGIN
        ================================= */
        .login-page { width: 100%; padding: 35px 25px 50px; }
        .login-wrapper {
            max-width: 1150px;
            width: 100%;
            min-height: 620px;
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
        .login-banner {
            background: linear-gradient(145deg, var(--sage-soft), #D1E0D1);
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .login-banner::before {
            content: "";
            position: absolute;
            width: 230px;
            height: 230px;
            background: rgba(255,255,255,0.4);
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }
        .login-banner-content { position: relative; z-index: 2; }

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
           BAGIAN KANAN (FORM LOGIN)
        ================================= */
        .login-form-container { padding: 45px 55px; display: flex; flex-direction: column; justify-content: center; }

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
        .form-subtitle { text-align: center; font-size: 13px; color: var(--muted); margin-bottom: 28px; }

        .alert-error {
            background: #fff0f0; color: #d63031; border: 1px solid #ffcaca;
            border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; font-size: 12px;
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
        .form-group input::placeholder { color: #a2a4b1; }
        .form-group input:focus, .form-group select:focus { border-color: var(--sage); box-shadow: 0 0 0 3px rgba(135,169,135,0.12); }
        .form-group select { cursor: pointer; }

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

        .btn-register {
            width: 100%; height: 49px; display: flex; align-items: center; justify-content: center; gap: 9px;
            border: 1.5px solid var(--sage); border-radius: 30px; text-decoration: none; color: var(--sage-dark); font-size: 13px; font-weight: 700; transition: 0.3s;
        }
        .btn-register:hover { background: var(--sage-soft); }

        /* =================================
           RESPONSIVE DESIGN
        ================================= */
        @media(max-width: 950px) {
            .nav-menu { position: static; transform: none; }
            .nav-spacer { display: none; }
            .login-wrapper { grid-template-columns: 0.9fr 1.1fr; }
            .login-banner { padding: 30px; }
            .login-form-container { padding: 35px 30px; }
            .banner-title { font-size: 29px; }
        }

        @media(max-width: 650px) {
            .navbar-container { padding: 12px 15px; flex-wrap: wrap; gap: 12px; justify-content: space-between; }
            .nav-menu { width: 100%; justify-content: center; gap: 20px; }
            .login-page { padding: 20px 12px 30px; }
            .login-wrapper { display: flex; flex-direction: column; border-radius: 17px; }
            .login-banner { padding: 27px 22px; }
            .banner-title { font-size: 28px; }
            .login-form-container { padding: 30px 22px; }
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
            <img src="../uploads/logokost.png" alt="Logo Aksara Kost" class="logo-icon">
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
     HALAMAN LOGIN
================================= -->
<main class="login-page">
    <div class="login-wrapper">

        <!-- BAGIAN KIRI -->
        <section class="login-banner">
            <div class="login-banner-content">
                <h1 class="banner-title">
                    Selamat Datang di
                    <span>AKSARA KOST</span>
                </h1>

                <p class="banner-description">
                    Temukan kost terbaik, nyaman, dan sesuai
                    dengan kebutuhanmu. Cari tempat tinggal
                    impianmu dengan mudah, cepat, dan aman.
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

        <!-- BAGIAN KANAN FORM LOGIN -->
        <section class="login-form-container">
            <div class="form-logo">
                <div class="form-logo-icon">
                    <img src="../uploads/logokost.png" alt="Logo Aksara Kost">
                </div>
            </div>

            <h1 class="form-title">
                Login ke <span>AKSARA KOST</span>
            </h1>

            <p class="form-subtitle">
                Masuk untuk melanjutkan ke akun Anda
            </p>

            <!-- PESAN ERROR -->
            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- FORM LOGIN -->
            <form action="" method="POST">
                <!-- USERNAME -->
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="username" placeholder="Masukkan username" value="<?= htmlspecialchars($username_input); ?>" autocomplete="username" required>
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" id="password" placeholder="Masukkan password" autocomplete="current-password" required>
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
                            <option value="" disabled <?= $role_input === '' ? 'selected' : ''; ?>>Pilih Role</option>
                            <option value="Admin" <?= $role_input === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="Owner" <?= $role_input === 'Owner' ? 'selected' : ''; ?>>Owner</option>
                            <option value="User" <?= $role_input === 'User' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                </div>

                <!-- TOMBOL LOGIN -->
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i> &nbsp; Login
                </button>
            </form>

            <!-- PEMISAH -->
            <div class="or-divider">atau</div>

            <!-- TOMBOL DAFTAR -->
            <a href="register.php" class="btn-register">
                <i class="fa-solid fa-user-plus"></i> Daftar Akun Baru
            </a>
        </section>

    </div>
</main>

<!-- =================================
     JAVASCRIPT PASSWORD
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