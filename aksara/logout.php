<?php
session_start();

// Menghapus semua session pengguna yang sedang login
session_unset();
session_destroy();

// Langsung mengarahkan ke halaman Beranda utama (index.php)
header("Location: beranda.php");
exit;