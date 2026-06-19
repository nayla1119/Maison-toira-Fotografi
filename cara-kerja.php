<?php
session_start();
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cara Kerja - Maison Étoira</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            padding-top: 50px; /* Sesuaikan dengan tinggi navbar */
            background-color: #f8f9fa; 
            color: #111; 
        }
        
        .container { max-width: 850px; margin: 50px auto; padding: 0 20px; }
        .container h1 { font-family: 'Playfair Display', serif; text-align: center; font-size: 2.8rem; margin-bottom: 50px; font-weight: 600; }

        .step-row { 
            display: flex; 
            gap: 30px; 
            margin-bottom: 30px; 
            align-items: flex-start; 
            background: #fff; 
            padding: 30px; 
            border-radius: 16px; 
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            transition: transform 0.3s;
        }
        .step-row:hover { transform: translateY(-3px); }
        
        .step-number { background: #111; color: #fff; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem; flex-shrink: 0; }
        .step-content h3 { margin: 0 0 8px 0; font-size: 1.25rem; font-weight: 700; color: #111; }
        .step-content p { margin: 0; color: #555; line-height: 1.6; font-size: 0.95rem; }
    </style>
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <main class="container">
        <h1>Alur Pemesanan Maison Étoira</h1>

        <div class="step-row">
            <div class="step-number">1</div>
            <div class="step-content">
                <h3>Pilih Paket & Fotografer</h3>
                <p>Jelajahi halaman katalog paket layanan kami yang sesuai dengan kebutuhanmu, lalu tentukan fotografer andalan dengan melihat portofolio estetika mereka yang tertera di sistem.</p>
            </div>
        </div>

        <div class="step-row">
            <div class="step-number">2</div>
            <div class="step-content">
                <h3>Isi Formulir & Jadwal</h3>
                <p>Tentukan tanggal dan jam pelaksanaan sesi pemotretan. Pastikan data diri dan nomor kontak yang kamu masukkan aktif agar memudahkan koordinasi teknis nantinya.</p>
            </div>
        </div>

        <div class="step-row">
            <div class="step-number">3</div>
            <div class="step-content">
                <h3>Lakukan Pembayaran</h3>
                <p>Kirimkan pembayaran sesuai invoice tagihan ke rekening resmi studio Maison Étoira, kemudian upload bukti transfernya langsung via dasbor akun pelangganmu.</p>
            </div>
        </div>

        <div class="step-row">
            <div class="step-number">4</div>
            <div class="step-content">
                <h3>Pantau Status & Sesi Foto</h3>
                <p>Setelah admin memvalidasi pembayaran, status pesananmu akan menyala hijau di halaman tracker. Sesi foto siap dilaksanakan, dan hasil karya terbaik akan diserahkan setelah proses editing selesai!</p>
            </div>
        </div>
    </main>

</body>
</html>