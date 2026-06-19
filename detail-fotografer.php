<?php
// 1. HUBUNGKAN KONEKSI DATABASE
include 'koneksi.php';

// 2. AMBIL ID FOTOGRAFER DARI URL
$id_fotografer = isset($_GET['id']) ? intval($_GET['id']) : 1;

// 3. QUERY UTAMA: AMBIL PROFIL FOTOGRAFER (JOIN USERS UNTUK NAMA)
$query_profil = "SELECT p.*, u.nama, u.email, u.nomor_telepon, u.foto_profile
                 FROM photographers p 
                 JOIN users u ON p.id_user = u.id_user 
                 WHERE p.id_fotografer = '$id_fotografer'";

$result_profil = mysqli_query($koneksi, $query_profil) or die(mysqli_error($koneksi));
$data = mysqli_fetch_assoc($result_profil);

// Jika ID tidak ada di database, hentikan sistem
if (!$data) {
    die("<h3 style='text-align:center; margin-top:50px;'>Maaf, profil fotografer tidak ditemukan!</h3>");
}

// 4. QUERY SUB-DATA UNTUK SISTEM TAB
// a. Ambil Portofolio
$query_portofolio = "SELECT * FROM portofolio WHERE id_fotografer = '$id_fotografer' ORDER BY id_portofolio DESC";

$result_portofolio = mysqli_query($koneksi, $query_portofolio) or die(mysqli_error($koneksi));

// b. Ambil Paket Layanan
$query_paket = "SELECT * FROM packages WHERE id_fotografer = $id_fotografer ORDER BY id_package ASC";
$result_paket = mysqli_query($koneksi, $query_paket);

// c. Ambil Ulasan/Reviews (JOIN Users untuk tahu nama pelanggan yang kasih ulasan)
$query_ulasan = "SELECT r.*, u.nama AS nama_pelanggan 
                 FROM review_fotografer r 
                 JOIN users u ON r.id_user = u.id_user 
                 JOIN bookings b ON r.id_booking = b.id_booking
                 WHERE b.id_fotografer = '$id_fotografer'
                 ORDER BY r.tanggal_review DESC";

$result_ulasan = mysqli_query($koneksi, $query_ulasan) or die(mysqli_error($koneksi));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail <?php echo htmlspecialchars($data['nama']); ?> - Maison Étoira</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght=0,600;1,400&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <main class="detail-container" style="padding: 120px 5% 60px 5%;">
        
        <nav class="breadcrumbs" style="margin-bottom: 30px; font-size: 0.9rem;">
            <a href="index.php" style="color: var(--text-muted); text-decoration: none;">Beranda</a>
            <span class="divider" style="margin: 0 8px; color: var(--text-muted); ">/</span>
            <a href="fotografer.php" style="color: var(--text-muted); text-decoration: none;">Fotografer</a>
            <span class="divider" style="margin: 0 8px; color: var(--text-muted);">/</span>
            <span class="current" style="color: var(--text-color); font-weight: 600;"><?php echo htmlspecialchars($data['nama']); ?></span>
        </nav>

        <section class="photographer-profile-hero">
            <div class="profile-hero-left">
                <p>Debug Nama File: <?php echo $data['foto_profile']; ?></p>
                <?php 
                $foto_profil = !empty($data['profile_image']) ? 'uploads/foto_profil/' . $data['foto_profile'] : 'assets/img/photographers/default-avatar.png';
                ?>
                <img src="<?php echo $foto_profil; ?>" alt="<?php echo htmlspecialchars($data['nama']); ?>">
            </div>
            
            <div class="profile-hero-right">
                <h1 class="photographer-name"><?php echo htmlspecialchars($data['nama']); ?></h1>
                
                <div class="meta-info-row">
                    <div class="meta-item rating">
                        <span class="star-icon">★</span>
                        <strong><?php echo htmlspecialchars($data['rating']); ?></strong> 
                        <span class="muted">(Pengalaman <?php echo htmlspecialchars($data['experience']); ?>)</span>
                    </div>
                    <div class="meta-item location">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <span><?php echo htmlspecialchars($data['location']); ?></span>
                    </div>
                </div>

                <div class="about-section">
                    <h3>Tentang Fotografer</h3>
                    <p><?php echo nl2br(htmlspecialchars($data['description'])); ?></p>
                </div>

                <div class="action-buttons-row">
                    <a href="booking.php?photographer_id=<?php echo $data['id_fotografer']; ?>" class="btn-action-order" style="text-decoration: none; text-align: center;">Pesan Sekarang</a>
<a href="https://wa.me/<?php echo htmlspecialchars($data['nomor_telepon']); ?>" target="_blank" class="btn-action-chat" style="text-decoration: none; text-align: center;">Hubungi</a>
                </div>
            </div>
        </section>

        <section class="tabs-section" style="margin-top: 50px;">
            <div class="tabs-header-bar">
                <button class="tab-btn active" data-tab="portofolio">Portofolio</button>
                <button class="tab-btn" data-tab="paket">Paket Layanan</button>
                <button class="tab-btn" data-tab="ulasan">Ulasan</button>
            </div>

            <div class="tabs-content-body" style="margin-top: 30px;">
                
                <div class="tab-panel active" id="portofolio">
                    <div class="portfolio-grid">
                        <?php 
                        if (mysqli_num_rows($result_portofolio) > 0) {
                            while ($porto = mysqli_fetch_assoc($result_portofolio)) {
                        ?>
                                <div class="portfolio-item" style="position: relative; group">
                                    <img src="assets/img/portfolio/<?php echo $porto['gambar']; ?>" alt="<?php echo htmlspecialchars($porto['judul']); ?>">
                                    <div class="portfolio-overlay" style="padding: 15px; background: rgba(0,0,0,0.6); color: #fff; position: absolute; bottom: 0; left: 0; right: 0; border-radius: 0 0 8px 8px;">
                                        <h4 style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($porto['judul']); ?></h4>
                                        <p style="margin: 5px 0 0 0; font-size: 0.8rem; color: #ddd;"><?php echo htmlspecialchars($porto['deskripsi']); ?></p>
                                    </div>
                                </div>
                        <?php 
                            }
                        } else {
                            echo "<p class='muted-text' style='text-align:center; grid-column: 1/-1;'>Belum ada foto portofolio yang diunggah.</p>";
                        }
                        ?>
                    </div>
                </div>

                <div class="tab-panel" id="paket">
                    <div class="service-packages-list" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                        <?php 
                        if (mysqli_num_rows($result_paket) > 0) {
                            while ($paket = mysqli_fetch_assoc($result_paket)) {
                        ?>
                                <div class="package-item-card" style="border: 1px solid var(--border-color); padding: 25px; border-radius: 12px; background: var(--card-bg);">
                                    <h4 style="font-size: 1.3rem; margin-bottom: 10px;"><?php echo htmlspecialchars($paket['package_name']); ?></h4>
                                    <div class="price" style="font-size: 1.6rem; font-weight: 700; color: var(--accent-color); margin-bottom: 5px;">
                                        Rp <?php echo number_format($paket['price'], 0, ',', '.'); ?>
                                    </div>
                                    <div class="duration" style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 15px;">
                                        ⏱️ Durasi: <?php echo htmlspecialchars($paket['duration']); ?>
                                    </div>
                                    <p style="font-size: 0.95rem; line-height: 1.5; color: var(--text-color);"><?php echo nl2br(htmlspecialchars($paket['description'])); ?></p>
                                    
                                    <a href="booking.php?photographer_id=<?php echo $id_fotografer; ?>&package_id=<?php echo $paket['id_package']; ?>" class="btn btn-primary" style="display:block; text-align:center; margin-top:20px; text-decoration:none; font-size:0.9rem; padding:10px;">Pilih Paket</a>
                                </div>
                        <?php 
                            }
                        } else {
                            echo "<p class='muted-text' style='text-align:center; width: 100%;'>Belum menyediakan paket layanan khusus.</p>";
                        }
                        ?>
                    </div>
                </div>

                <div class="tab-panel" id="ulasan">
                    <div class="reviews-wrapper" style="display: flex; flex-direction: column; gap: 15px;">
                        <?php 
                        if (mysqli_num_rows($result_ulasan) > 0) {
                            while ($review = mysqli_fetch_assoc($result_ulasan)) {
                        ?>
                                <div class="review-comment-card" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                                    <div class="review-user-info" style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                        <strong><?php echo htmlspecialchars($review['nama_pelanggan']); ?></strong>
                                        <span class="stars" style="color: #ffc107;">
                                            <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                        </span>
                                    </div>
                                    <p style="margin: 0; font-size: 0.95rem; color: var(--text-color);"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    <small style="color: var(--text-muted); display: block; margin-top: 5px;">
                                        📅 Diulas pada: <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                                    </small>
                                </div>
                        <?php 
                            }
                        } else {
                            echo "<p class='muted-text' style='text-align:center;'>Belum ada ulasan dari pelanggan.</p>";
                        }
                        ?>
                    </div>
                </div>

            </div>
        </section>
    </main>

    <?php include 'components/footer.php'; ?>

    <script>
        // Logika Pengendali Tab Menu Interaktif
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-tab');
                
                // Matikan tab & panel aktif sebelumnya
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
                
                // Aktifkan tab & panel yang diklik
                button.classList.add('active');
                document.getElementById(targetTab).classList.add('active');
            });
        });
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>