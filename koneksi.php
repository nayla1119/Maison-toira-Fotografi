<?php
// Pengaturan koneksi database untuk lokal (XAMPP)
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "maison_etoira_fotografi"; // Pastikan nama di phpMyAdmin sama dengan ini ya, Nay

// Menghubungkan ke MySQL
$koneksi = mysqli_connect($host, $user, $pass, $db_name);

// Periksa apakah koneksi berhasil atau gagal
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>