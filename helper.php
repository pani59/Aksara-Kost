<?php
/**
 * Fungsi untuk menampilkan gambar dengan aman (Fallback otomatis jika file tidak ada)
 * 
 * @param string $nama_file Nama file yang disimpan di database
 * @param string $folder Direktori penyimpanan (default: '../uploads/')
 * @param string $default_file Nama file default jika foto utama tidak ditemukan
 * @return string Path lengkap file gambar yang valid
 */
function tampilkan_gambar($nama_file, $folder = '../uploads/', $default_file = 'profil.jpeg') {
    // Bersihkan nama file dari spasi atau karakter kosong
    $nama_file = trim($nama_file);
    
    // Tentukan path fisik di komputer/server
    // Sesuaikan path folder jika file helper dipanggil dari folder yang berbeda
    $path_fisik = $folder . $nama_file;
    
    // Cek apakah nama file ada di database DAN file fisiknya benar-benar ada di folder uploads
    if (!empty($nama_file) && file_exists($path_fisik)) {
        return $path_fisik;
    } else {
        // Jika tidak ada, gunakan file default yang kamu inginkan
        return $folder . $default_file;
    }
}
?>