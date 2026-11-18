<?php
session_start();

// 1. Kosongkan semua variabel session
$_SESSION = array();

// 2. Hapus cookie session dari browser jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan seluruh session
session_destroy();

// 4. Alihkan halaman ke tampilan Kos Vani (kosvani/index.php)
header("Location: ../index.php");
exit;
?>