<?php
// Hubungkan ke database.php yang ada di luar folder kosvani
include '../database.php'; 

if (isset($_POST['submit_review'])) {
    $booking_id = $_POST['booking_id'];
    $rating     = $_POST['rating'];
    $komentar   = mysqli_real_escape_string($conn, $_POST['komentar']);

    // INSERT data ke tabel review
    $query = "INSERT INTO review (booking_id, rating, komentar, dibuat_pada) 
              VALUES ('$booking_id', '$rating', '$komentar', NOW())";

    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>
                alert('Ulasan berhasil dikirim!');
                window.location.href='profil.php';
              </script>";
    } else {
        echo "Gagal menyimpan ulasan: " . mysqli_error($conn);
    }
}
?>