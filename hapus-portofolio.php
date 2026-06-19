<?php
session_start();
include 'koneksi.php';

// Proteksi agar tidak diakses sembarangan
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);
    
    // 1. Ambil nama file dulu supaya bisa dihapus dari folder
    $query_foto = mysqli_query($koneksi, "SELECT gambar FROM portofolio WHERE id_portofolio = '$id_hapus'");
    $data_foto = mysqli_fetch_assoc($query_foto);
    $nama_file = $data_foto['gambar'];

    // 2. Hapus file dari folder assets
    $path_file = "assets/img/portfolio/" . $nama_file;
    if (file_exists($path_file)) {
        unlink($path_file); // Perintah hapus file
    }

    // 3. Hapus data dari database
    $query_delete = mysqli_query($koneksi, "DELETE FROM portofolio WHERE id_portofolio = '$id_hapus'");

    if ($query_delete) {
        header("Location: fotografer-portofolio.php?pesan=berhasil_dihapus");
    } else {
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }
}
?>