<?php
session_start();
include 'koneksi.php';

// Proteksi: Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// Mengambil ID Booking dari URL (contoh: status-pesanan.php?id=1)
if (!isset($_GET['id'])) {
    echo "ID Pesanan tidak ditemukan.";
    exit();
}

$id_booking = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_user = $_SESSION['id_user'];

// Query mengambil data booking spesifik milik user yang sedang login
$query = "SELECT b.*, 
                 u_photo.nama AS nama_fotografer, 
                 p.package_name, p.price 
          FROM bookings b
          JOIN photographers ph ON b.id_photographer = ph.id_photographer
          JOIN users u_photo ON ph.id_user = u_photo.id_user
          JOIN packages p ON b.id_package = p.id_package
          WHERE b.id_booking = '$id_booking' AND b.id_customer = '$id_user'";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) === 0) {
    echo "Pesanan tidak ditemukan atau Anda tidak memiliki akses ke halaman ini.";
    exit();
}

$data = mysqli_fetch_assoc($result);
$status = $data['status']; // Mengambil status aktual: pending, unpaid, confirmed, atau completed
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan - Maison Étoira</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: var(--bg-secondary, #f9f9f9);
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* --- NAVBAR STYLE --- */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 15px 50px;
            border-bottom: 1px solid #eee;
        }
        .navbar-logo { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700; color: #000; text-decoration: none; }
        .navbar-menu { display: flex; gap: 30px; list-style: none; margin: 0; padding: 0; }
        .navbar-menu a { text-decoration: none; color: #555; font-weight: 500; font-size: 0.95rem; }
        .navbar-profile { font-size: 1.3rem; color: #333; }

        /* --- MAIN CONTAINER --- */
        .status-container {
            display: flex;
            justify-content: center;
            padding: 50px 20px;
        }
        .status-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        /* --- TIMELINE TRACKER COMPONENT (Sesuai Wireframe) --- */
        .timeline {
            position: relative;
            margin: 10px 0 35px 20px;
            padding-left: 35px;
            border-left: 2px solid #e5e7eb;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        .timeline-item { position: relative; }
        
        /* Ikon lingkaran indikator */
        .timeline-icon {
            position: absolute;
            left: -53px;
            top: 2px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: #9ca3af;
            transition: 0.3s;
        }
        .timeline-content h4 { margin: 0 0 4px 0; font-size: 0.95rem; color: #374151; font-weight: 700; }
        .timeline-content p { margin: 0; font-size: 0.85rem; color: #6b7280; }

        /* KONDISI AKTIF BERDASARKAN STATUS DATABASE */
        /* Tahap 1: Pesanan Dibuat (Selalu aktif jika data ada) */
        .step-1 .timeline-icon { background: #22c55e; border-color: #22c55e; color: #fff; }
        .step-1 h4 { color: #111; }

        /* Tahap 2: Pembayaran */
        <?php if($status != 'pending'): ?>
            .step-2 .timeline-icon { background: #eab308; border-color: #eab308; color: #fff; }
            .step-2 h4 { color: #111; }
        <?php endif; ?>

        /* Tahap 3: Dikonfirmasi Fotografer */
        <?php if($status == 'confirmed' || $status == 'completed'): ?>
            .step-3 .timeline-icon { background: #3b82f6; border-color: #3b82f6; color: #fff; }
            .step-3 h4 { color: #111; }
        <?php endif; ?>

        /* Tahap 4: Selesai */
        <?php if($status == 'completed'): ?>
            .step-4 .timeline-icon { background: #10b981; border-color: #10b981; color: #fff; }
            .step-4 h4 { color: #111; }
        <?php endif; ?>

        /* Aturan garis vertikal yang menyala jika progres berjalan */
        .timeline::before {
            content: '';
            position: absolute;
            left: -2px;
            top: 15px;
            width: 2px;
            background: #22c55e;
            /* Tinggi garis hijau mengikuti level status */
            height: <?php 
                if($status == 'pending') echo '0%';
                elseif($status == 'unpaid') echo '33%';
                elseif($status == 'confirmed') echo '66%';
                else echo '100%';
            ?>;
            transition: height 0.5s ease-in-out;
        }

        /* --- DETAIL PESANAN CONTAINER --- */
        .detail-box {
            background-color: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .detail-box h3 { margin-top: 0; margin-bottom: 18px; font-size: 1.1rem; color: #111; font-weight: 700; }
        .detail-row { display: flex; margin-bottom: 12px; font-size: 0.9rem; }
        .detail-label { width: 120px; color: #6b7280; font-weight: 500; }
        .detail-value { flex-grow: 1; color: #111; font-weight: 500; }
        .detail-value.price-tag { font-weight: 700; }

        /* --- TOMBOL AKSI HITAM MANTAP --- */
        .btn-action {
            display: block;
            text-align: center;
            width: 100%;
            padding: 14px;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        .btn-action:hover { background: #333; }
    </style>
</head>
<body>

    <header class="navbar">
        <a href="index.php" class="navbar-logo">LOGO</a>
        <nav>
            <ul class="navbar-menu">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="dashboard.php" style="color:#000; font-weight:600;">Pesanan</a></li>
            </ul>
        </nav>
        <a href="dashboard.php" class="navbar-profile"><i class="fa-regular fa-user"></i></a>
    </header>

    <main class="status-container">
        <div class="status-card">
            
            <ul class="timeline">
                <li class="timeline-item step-1">
                    <div class="timeline-icon"><i class="fa-solid fa-check"></i></div>
                    <div class="timeline-content">
                        <h4>Pesanan Dibuat</h4>
                        <p><?php echo date('d M Y, H:i', strtotime($data['created_at'])); ?> WIB</p>
                    </div>
                </li>
                <li class="timeline-item step-2">
                    <div class="timeline-icon"><i class="fa-solid fa-dollar-sign"></i></div>
                    <div class="timeline-content">
                        <h4>Pembayaran</h4>
                        <p><?php echo ($status == 'pending') ? 'Menunggu Pembayaran' : 'Pembayaran Dikonfirmasi'; ?></p>
                    </div>
                </li>
                <li class="timeline-item step-3">
                    <div class="timeline-icon"><i class="fa-solid fa-camera"></i></div>
                    <div class="timeline-content">
                        <h4>Pesanan Dikonfirmasi</h4>
                        <p><?php echo ($status == 'confirmed' || $status == 'completed') ? 'Disetujui oleh fotografer' : 'Menunggu konfirmasi dari fotografer'; ?></p>
                    </div>
                </li>
                <li class="timeline-item step-4">
                    <div class="timeline-icon"><i class="fa-solid fa-flag-checkered"></i></div>
                    <div class="timeline-content">
                        <h4>Selesai</h4>
                        <p><?php echo ($status == 'completed') ? 'Sesi foto selesai & file siap' : 'Pesanan selesai'; ?></p>
                    </div>
                </li>
            </ul>

            <div class="detail-box">
                <h3>Detail Pesanan</h3>
                <div class="detail-row">
                    <div class="detail-label">Fotografer</div>
                    <div class="detail-value">: <?php echo htmlspecialchars($data['nama_fotografer']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Paket</div>
                    <div class="detail-value">: <strong><?php echo htmlspecialchars($data['package_name']); ?></strong></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Tanggal</div>
                    <div class="detail-value">: <?php echo date('d M Y', strtotime($data['created_at'])); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Total</div>
                    <div class="detail-value price-tag">: Rp <?php echo number_format($data['price'], 0, ',', '.'); ?></div>
                </div>
            </div>

            <a href="dashboard.php" class="btn-action">Lihat Detail Pesanan</a>

        </div>
    </main>

</body>
</html>