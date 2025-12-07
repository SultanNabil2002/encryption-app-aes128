<?php
// AplikasiWebKripto/config/config.php

// Pengaturan Database
define('DB_HOST', 'localhost'); // Biasanya 'localhost' atau '127.0.0.1'
define('DB_USER', 'root');      // Ganti dengan username database Anda
define('DB_PASS', '');          // Ganti dengan password database Anda
define('DB_NAME', 'db_kripto_web'); // Nama database yang Anda pilih

// Membuat koneksi MySQLi
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($mysqli->connect_error) {
    // Jangan tampilkan error detail di production, cukup log atau pesan umum
    // Untuk development, ini bisa membantu:
    die("Koneksi database gagal: " . $mysqli->connect_error);
    // Di production, mungkin lebih baik:
    // die("Terjadi masalah koneksi ke database. Silakan coba lagi nanti.");
}

// Mengatur charset ke utf8 (rekomendasi)
if (!$mysqli->set_charset("utf8mb4")) {
    // printf("Error loading character set utf8mb4: %s\n", $mysqli->error);
    // exit();
    // Untuk production, mungkin cukup log error ini
}

?>