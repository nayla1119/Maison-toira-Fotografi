<?php
// 1. AKTIFKAN SESSION PHP & HUBUNGKAN DATABASE
session_start();
include 'koneksi.php';

// 2. QUERY UNTUK MENGAMBIL DATA FOTOGRAFER TERPOPULER
// Mengambil data terpopuler berdasarkan rating tertinggi (maksimal 3 kartu di beranda)
$query = "SELECT p.*, u.nama AS name 
          FROM photographers p 
          JOIN users u ON p.id_user = u.id_user 
          WHERE u.role = 'fotografer' 
          ORDER BY p.rating DESC 
          LIMIT 3";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maison Étoira - Jasa Fotografer Profesional</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <header class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content">
                <span class="badge">Professional Photography Platform</span>
                <h1>Menangkap Esensi Momen Dalam Frame Abadi</h1>
                <p>Platform pemesanan jasa fotografer terbaik untuk kebutuhan personal, wisuda, prewedding, hingga dokumentasi bisnis komersial dengan hasil estetik bernuansa sinematik.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary">Pesan Sekarang</a>
                    <a href="fotografer.php" class="btn btn-secondary">Eksplorasi Fotografer</a>
                </div>
            </div>
            <div class="hero-image-wrapper">
                <div class="aesthetic-box">
                    <div class="inner-frame">
                        <span class="lens-art">Maison Étoira Studio</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="photographers" id="photographers">
        <div class="section-header-flex">
            <div>
                <h2>Fotografer Terpopuler</h2>
                <p>Temukan deretan fotografer terbaik bulan ini dengan rating tertinggi dari pelanggan kami.</p>
            </div>
            <div class="slider-controls">
                <button class="arrow-btn" id="viewAllPhotographers" title="Lihat Semua Fotografer" onclick="window.location.href='fotografer.php'" style="background:none; border:1px solid var(--border-color); padding:10px 20px; border-radius:8px; cursor:pointer; color:var(--text-color); display:flex; align-items:center; gap:8px; font-weight:600;">
                    <span>Lihat Semua</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </div>
        </div>
        
        <div class="photographer-grid">
            <?php 
            // Loop data fotografer dari database MySQL
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $foto_profil = !empty($row['profile_image']) ? 'assets/img/photographers/' . $row['profile_image'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=500';
            ?>
                    <div class="photographer-card" onclick="window.location.href='detail-fotografer.php?id=<?php echo htmlspecialchars($row['id_fotografer'] ?? ''); ?>'" style="cursor: pointer;">
                        <div class="photo-placeholder">
                            <img src="<?php echo $foto_profil; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                            <div class="rating-badge">⭐ <?php echo $row['rating']; ?></div>
                        </div>
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <span class="spec-tag"><?php echo htmlspecialchars($row['location']); ?></span>
                            <p><?php echo htmlspecialchars(substr($row['description'], 0, 80)) . '...'; ?></p>
                            <small style="color: var(--text-muted); font-weight: 500; display:block; margin-top:10px;">Pengalaman: <?php echo htmlspecialchars($row['experience']); ?></small>
                        </div>
                    </div>
            <?php 
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center; color: var(--text-muted); padding:20px 0;'>Belum ada data fotografer di database.</p>";
            }
            ?>
        </div>
    </section>

    <section class="packages" id="packages" style="max-width:1300px; margin:0 auto; padding:60px 5%;">
        <div class="section-header" style="margin-bottom:40px; text-align:center;">
            <h2>Pilihan Paket Kreatif</h2>
            <p style="color:var(--text-muted);">Pilih paket harga transparan yang dirancang sesuai dengan skala kebutuhan acara Anda.</p>
        </div>
        <div class="packages-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:30px;">
            <div class="package-card" style="border:1px solid var(--border-color); padding:30px; border-radius:16px; background:var(--card-bg);">
                <h3>Starter Pack</h3>
                <div class="price" style="font-size:1.8rem; font-weight:700; margin:15px 0; color:var(--accent-color);">Rp 750.000</div>
                <ul style="list-style:none; color:var(--text-muted); display:flex; flex-direction:column; gap:10px;">
                    <li>✓ 1 Fotografer Profesional</li>
                    <li>✓ 2 Jam Sesi Pemotretan</li>
                    <li>✓ 25 Foto Hasil Edit Terbaik</li>
                    <li>✓ Semua File Mentah (Google Drive)</li>
                </ul>
            </div>
            <div class="package-card premium" style="border:2px solid var(--text-color); padding:30px; border-radius:16px; background:var(--card-bg); position:relative;">
                <div class="p-badge" style="position:absolute; top:-15px; right:20px; background:var(--text-color); color:var(--bg-color); padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:700;">Paling Populer</div>
                <h3>Exclusive Pack</h3>
                <div class="price" style="font-size:1.8rem; font-weight:700; margin:15px 0; color:var(--accent-color);">Rp 1.800.000</div>
                <ul style="list-style:none; color:var(--text-muted); display:flex; flex-direction:column; gap:10px;">
                    <li>✓ 2 Fotografer Profesional</li>
                    <li>✓ 5 Jam Sesi Pemotretan</li>
                    <li>✓ 60 Foto Hasil Edit Premium</li>
                    <li>✓ Cetak Flashdisk Khusus + Cetak Fisik 10R</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="workflow" id="workflow" style="max-width:1300px; margin:0 auto; padding:60px 5%;">
        <div class="section-header" style="margin-bottom:40px; text-align:center;">
            <h2>Cara Kerja Sistem</h2>
            <p style="color:var(--text-muted);">Alur ringkas pemesanan jasa fotografer dari awal hingga penyerahan berkas hasil karya.</p>
        </div>
        <div class="workflow-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:30px;">
            <div class="workflow-item-card" style="background:var(--card-bg); padding:30px; border-radius:16px; border:1px solid var(--border-color);">
                <div class="num" style="font-size:2rem; font-family:'Playfair Display', serif; color:var(--accent-color); font-weight:700; margin-bottom:10px;">01</div>
                <h3>Pilih & Booking</h3>
                <p style="color:var(--text-muted); font-size:0.95rem; margin-top:8px;">Cari profil fotografer favoritmu lalu kirim form jadwal pengajuan lewat sistem.</p>
            </div>
            <div class="workflow-item-card" style="background:var(--card-bg); padding:30px; border-radius:16px; border:1px solid var(--border-color);">
                <div class="num" style="font-size:2rem; font-family:'Playfair Display', serif; color:var(--accent-color); font-weight:700; margin-bottom:10px;">02</div>
                <h3>Konfirmasi & Bayar</h3>
                <p style="color:var(--text-muted); font-size:0.95rem; margin-top:8px;">Fotografer menyetujui, admin memvalidasi pembayaran uang muka (DP) Anda.</p>
            </div>
            <div class="workflow-item-card" style="background:var(--card-bg); padding:30px; border-radius:16px; border:1px solid var(--border-color);">
                <div class="num" style="font-size:2rem; font-family:'Playfair Display', serif; color:var(--accent-color); font-weight:700; margin-bottom:10px;">03</div>
                <h3>Pemotretan & Unduh</h3>
                <p style="color:var(--text-muted); font-size:0.95rem; margin-top:8px;">Sesi foto selesai dikerjakan, hasil foto diunggah langsung ke akun dashboard Anda.</p>
            </div>
        </div>
    </section>

    <section class="about" id="about" style="max-width:1300px; margin:0 auto; padding:60px 5% 100px 5%;">
        <div class="about-container" style="background:var(--card-bg); border:1px solid var(--border-color); padding:40px; border-radius:24px; text-align:center;">
            <div class="about-text" style="max-width:800px; margin:0 auto;">
                <h2 style="margin-bottom:15px;">Tentang Maison Étoira</h2>
                <p style="color:var(--text-muted); line-height:1.7;">Maison Étoira didirikan untuk merevolusi ekosistem industri kreatif fotografi. Kami menggabungkan kemudahan manajemen sistem informasi dengan cita rasa seni digital modern, menghadirkan transparansi jadwal dan keamanan transaksi bagi pelanggan maupun mitra fotografer profesional.</p>
            </div>
        </div>
    </section>

    <?php include 'components/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>