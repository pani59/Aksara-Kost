<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/database.php';

// 1. Wajibkan user login terlebih dahulu
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ==========================================================
// VALIDASI TAMBAHAN: CEK STATUS VERIFIKASI USER
// ==========================================================
$q_cek_user = mysqli_query($conn, "SELECT status_verifikasi FROM user WHERE user_id = '$user_id' LIMIT 1");
$data_user  = mysqli_fetch_assoc($q_cek_user);

if ($data_user && $data_user['status_verifikasi'] === 'Menunggu') {
    $kos_id_asal = isset($_POST['kos_id']) ? (int)$_POST['kos_id'] : 0;
    $redirect_url = ($kos_id_asal > 0) ? "detail.php?id=" . $kos_id_asal : "index.php";
    
    echo "<script>
            alert('Akun Anda masih Menunggu Verifikasi dari Admin. Anda tidak dapat melakukan booking kost sebelum akun disetujui.'); 
            window.location.href='$redirect_url';
          </script>";
    exit;
}
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kos_id         = (int)$_POST['kos_id'];
    $tanggal_survey = mysqli_real_escape_string($conn, trim($_POST['tanggal_survey']));

    if ($kos_id > 0 && !empty($tanggal_survey)) {
        // 2. Cari kamar pertama yang terdaftar di kos tersebut
        $query_kamar = mysqli_query($conn, "SELECT kamar_id FROM kamar WHERE kos_id = '$kos_id' LIMIT 1");
        $kamar       = mysqli_fetch_assoc($query_kamar);

        if ($kamar) {
            $kamar_id = $kamar['kamar_id'];

            // 3. Simpan data booking ke database
            $sql = "INSERT INTO booking (kamar_id, user_id, tanggal_survey, status_booking, dibuat_pada) 
                    VALUES ('$kamar_id', '$user_id', '$tanggal_survey', 'Menunggu', NOW())";

            if (mysqli_query($conn, $sql)) {
                // 4. Berhasil booking, arahkan langsung ke halaman Riwayat Pesanan
                header("Location: booking.php");
                exit;
            } else {
                echo "<script>alert('Gagal mengajukan booking: " . mysqli_error($conn) . "'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Maaf, belum ada kamar yang tersedia untuk kos ini.'); window.history.back();</script>";
            exit;
        }
    }
}

header("Location: index.php");
exit;