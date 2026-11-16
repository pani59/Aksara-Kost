<?php
session_start();
include '.php';

$email    = $_POST['email'];
$password = $_POST['password'];

// 1. Ambil data dari database
$query = mysqli_query($koneksi, "SELECT * FROM user WHERE Email='$email' AND Password='$password'");
$user  = mysqli_fetch_assoc($query);

// 2. Cek user dan simpan session
if ($user) {
    $_SESSION['is_login'] = true;
    $_SESSION['user_id']  = $user['id'] ?? 1; 
    $_SESSION['nama']     = $user['Nama'];
    $_SESSION['email']    = $user['Email'];
    $_SESSION['role']     = $user['Role'];

    // Cek Role dan Arahkan ke Halaman yang Sesuai
    $role = strtolower(trim($user['Role']));

    if ($role === 'owner') {
        // Mengarahkan keluar dari folder kosvani/ menuju dashboard.php di root
        header("Location: ../headerowner.php");
        exit();
    } elseif ($role === 'admin') {
        header("Location: ../dashboard_admin.php");
        exit();
    } else {
        header("Location: ../index.php");
        exit();
    }
} else {
    // Jika email/password salah
    echo "<script>alert('Email atau Password salah!'); window.location='login.php';</script>";
}
?>