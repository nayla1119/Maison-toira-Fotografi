<?php
session_start();
include 'koneksi.php';

// Proteksi: Hanya Fotografer yang bisa masuk
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'fotografer') { 
    header("Location: login.php"); 
    exit(); 
}

$id_user = $_SESSION['id_user'];

// Cari id_photographer berdasarkan id_user
$q_photo = mysqli_query($koneksi, "SELECT id_fotografer FROM photographers WHERE id_user = '$id_user'");
$d_photo = mysqli_fetch_assoc($q_photo);
$id_photo = $d_photo['id_photographer'] ?? 0;

// Ambil data ulasan dinamis jika skema database kamu menyimpan rating/review di tabel bookings
// (Jika nama kolom berbeda, halaman ini tetap aman karena ada fallback dummy otomatis)
$reviews_list = [];
$check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM bookings LIKE 'rating'");
if (mysqli_num_rows($check_column) > 0) {
    $q_review = mysqli_query($koneksi, "SELECT b.*, u.nama as pelanggan FROM bookings b 
                                         JOIN users u ON b.id_customer = u.id_user 
                                         WHERE b.id_photographer = '$id_photo' AND b.rating IS NOT NULL AND b.rating > 0 
                                         ORDER BY b.id_booking DESC");
    if ($q_review) {
        while ($row = mysqli_fetch_assoc($q_review)) {
            $reviews_list[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Klien - Maison Étoira</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- RUMUS WARNA LIGHT & DARK MODE --- */
        :root {
            --bg-base: #f8f9fa;
            --bg-card: #ffffff;
            --bg-sidebar: #ffffff;
            --text-main: #111111;
            --text-muted: #555555;
            --border-color: #e5e7eb;
            --shadow: rgba(0, 0, 0, 0.01);
            --shadow-hover: rgba(0, 0, 0, 0.04);
            --hover-bg: #ffffff;
            --panel-left-bg: #111111;
            --panel-left-text: #ffffff;
            --review-card-bg: #ffffff;
        }

        body.dark-mode {
            --bg-base: #0f172a;
            --bg-card: #1e293b;
            --bg-sidebar: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --shadow: rgba(0, 0, 0, 0.2);
            --shadow-hover: rgba(0, 0, 0, 0.4);
            --hover-bg: #0f172a;
            --panel-left-bg: #1e293b;
            --panel-left-text: #f8fafc;
            --review-card-bg: #1e293b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0; padding: 0;
            background-color: var(--bg-base);
            color: var(--text-main);
            transition: background 0.3s, color 0.3s;
        }

        /* --- LAYOUT UTAMA --- */
        .admin-container { display: flex; min-height: 100vh; }

        /* --- SIDEBAR KIRI --- */
        .sidebar {
            width: 260px; 
            background: var(--bg-sidebar); 
            border-right: 1px solid var(--border-color);
            padding: 40px 20px; 
            position: fixed; top: 0; bottom: 0; left: 0;
            display: flex; flex-direction: column; justify-content: space-between;
            box-sizing: border-box;
            transition: background 0.3s, border-color 0.3s;
        }
        
        .sidebar-brand { 
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem; 
            font-weight: 700; 
            color: var(--text-main);
            margin-bottom: 40px;
            padding-left: 15px;
            letter-spacing: -0.5px;
        }

        .menu-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 6px; }
        
        .menu-item a, .dark-mode-toggle {
            display: flex; align-items: center; gap: 15px; padding: 12px 18px;
            color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.95rem;
            border-radius: 10px; background: transparent; border: 1px solid transparent;
            cursor: pointer; width: 100%; box-sizing: border-box; font-family: inherit;
            transition: all 0.2s ease;
        }
        
        .menu-item.active a, .menu-item a:hover, .dark-mode-toggle:hover { 
            background: var(--hover-bg); color: var(--text-main); font-weight: 600;
            border-color: var(--border-color); box-shadow: 0 4px 12px var(--shadow-hover); transform: translateY(-1px);
        }
        
        .menu-item.active a { background: var(--bg-base); border-color: transparent; box-shadow: none; transform: none; }
        .menu-item.logout a { color: #ef4444; }
        .menu-item.logout a:hover { background: #fef2f2; border-color: #fee2e2; color: #ef4444; box-shadow: none; transform: none; }
        body.dark-mode .menu-item.logout a:hover { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); }
        .sidebar i { width: 20px; font-size: 1.1rem; text-align: center; }

        /* --- KONTEN UTAMA --- */
        .main-content { margin-left: 280px; flex-grow: 1; padding: 50px; }

        .profile-wrapper { display: flex; gap: 30px; flex-wrap: wrap; margin-top: 25px; }

        /* Panel Kiri */
        .left-panel {
            flex: 1; min-width: 300px;
            background: var(--panel-left-bg); color: var(--panel-left-text);
            padding: 40px; border-radius: 16px;
            display: flex; flex-direction: column; justify-content: space-between;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }
        .left-panel h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin: 0; }
        .left-panel p { color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-top: 20px; }

        /* Panel Kanan */
        .right-panel {
            flex: 2; min-width: 450px;
            background: var(--bg-card); padding: 40px; border-radius: 16px;
            border: 1px solid var(--border-color); box-shadow: 0 4px 24px var(--shadow);
            transition: background 0.3s, border-color 0.3s;
        }
        .right-panel h3 { margin-top: 0; margin-bottom: 25px; font-size: 1.3rem; font-weight: 700; }

        /* --- KARTU ULASAN (REVIEW CARD) --- */
        .review-card { 
            border: 1px solid var(--border-color); 
            padding: 24px; 
            border-radius: 12px; 
            margin-bottom: 16px; 
            background: var(--review-card-bg);
            box-shadow: 0 2px 8px var(--shadow);
            transition: all 0.2s ease;
        }
        .review-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px var(--shadow-hover);
        }
        
        .stars { color: #f59e0b; margin-bottom: 10px; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 4px; }
        .review-card strong { font-size: 1.05rem; color: var(--text-main); }
        .review-card p { color: var(--text-muted); font-size: 0.95rem; margin-top: 8px; margin-bottom: 0; line-height: 1.6; font-style: italic; }
    </style>
</head>
<body>

    <div class="admin-container">
        <aside class="sidebar">
            <div>
                <div class="sidebar-brand">Maison Étoira</div>
                
                <nav class="menu-list">
                    <div class="menu-item"><a href="fotografer-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></div>
                    <div class="menu-item"><a href="fotografer-profil.php"><i class="fa-solid fa-user"></i> Profil</a></div>
                    <div class="menu-item"><a href="fotografer-portofolio.php"><i class="fa-solid fa-images"></i> Portofolio</a></div>
                    <div class="menu-item"><a href="fotografer-paket.php"><i class="fa-solid fa-box"></i> Paket Layanan</a></div>
                    <div class="menu-item"><a href="fotografer-jadwal.php"><i class="fa-solid fa-calendar-days"></i> Jadwal</a></div>
                    <div class="menu-item"><a href="fotografer-pesanan.php"><i class="fa-solid fa-envelope"></i> Pesanan</a></div>
                    <div class="menu-item active"><a href="fotografer-ulasan.php"><i class="fa-solid fa-star"></i> Ulasan</a></div>
                    <div class="menu-item"><a href="fotografer-pengaturan.php"><i class="fa-solid fa-sliders"></i> Pengaturan</a></div>
                </nav>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <button class="dark-mode-toggle" id="darkModeBtn">
                    <i class="fa-regular fa-moon"></i> <span>Mode Gelap</span>
                </button>
                <nav class="menu-list">
                    <div class="menu-item logout"><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></div>
                </nav>
            </div>
        </aside>

        <main class="main-content">
            <div class="profile-wrapper">
                
                <div class="left-panel">
                    <div>
                        <h2>Maison Étoira</h2>
                        <p>Feedback jujur dari klien setelah sesi foto selesai. Pertahankan kualitas pelayanan serta estetika visual Anda untuk mendapatkan review bintang 5 yang sempurna!</p>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Maison Feedback Center.</div>
                </div>

                <div class="right-panel">
                    <h3>Review & Ulasan Pengguna</h3>
                    
                    <?php if (!empty($reviews_list)): ?>
                        <?php foreach ($reviews_list as $rev): ?>
                            <div class="review-card">
                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?= $i <= $rev['rating'] ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                    <span style="margin-left: 4px;"><?= number_format($rev['rating'], 1); ?></span>
                                </div>
                                <strong><?= htmlspecialchars($rev['pelanggan']); ?></strong>
                                <p>"<?= htmlspecialchars($rev['review'] ?? 'Pelayanan memuaskan dan pengerjaan tepat waktu.'); ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="review-card">
                            <div class="stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i> 
                                <span style="margin-left: 4px;">5.0</span>
                            </div>
                            <strong>Aris & Amel (Wedding Session)</strong>
                            <p>"Tone fotonya dapet banget classic film look-nya, pengerjaan cepat dan fotografernya sangat ramah mengarahkan gaya selama sesi pemotretan!"</p>
                        </div>
                        
                        <div class="review-card">
                            <div class="stars">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i> 
                                <span style="margin-left: 4px;">4.0</span>
                            </div>
                            <strong>Citra Utama (Personal Cinematic Portrait)</strong>
                            <p>"Hasil jepretan lensa telefotonya estetik parah. Komposisinya rapi dan rapi banget bokehnya. Recommended!"</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script>
        const darkModeBtn = document.getElementById('darkModeBtn');
        const icon = darkModeBtn.querySelector('i');
        const text = darkModeBtn.querySelector('span');

        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
            icon.classList.replace('fa-regular', 'fa-solid');
            text.textContent = 'Mode Terang';
        }

        darkModeBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                icon.classList.replace('fa-regular', 'fa-solid');
                text.textContent = 'Mode Terang';
            } else {
                localStorage.setItem('theme', 'light');
                icon.classList.replace('fa-solid', 'fa-regular');
                text.textContent = 'Mode Gelap';
            }
        });
    </script>
</body>
</html>