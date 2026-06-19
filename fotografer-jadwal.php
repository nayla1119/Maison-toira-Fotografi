<?php
session_start();
include 'koneksi.php';

// Proteksi: Hanya Fotografer yang bisa masuk
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'fotografer') { 
    header("Location: login.php"); 
    exit(); 
}

$id_user = $_SESSION['id_user'];

// Ambil id_fotografer milik fotografer ini
$q_photo = mysqli_query($koneksi, "SELECT id_fotografer FROM photographers WHERE id_user = '$id_user'");
$d_photo = mysqli_fetch_assoc($q_photo);
$id_photo = $d_photo['id_fotografer'] ?? 0;

// Ambil agenda pemotretan terkonfirmasi (Diselaraskan agar memfilter berdasarkan id_fotografer terkait)
$query_agenda = mysqli_query($koneksi, "SELECT b.*, u.nama FROM bookings b 
                                        JOIN users u ON b.id_customer = u.id_user 
                                        WHERE b.id_fotografer = '$id_photo' AND b.status = 'confirmed'
                                        ORDER BY b.id_booking DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kerja - Maison Étoira</title>
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
            --schedule-bg: #f9f9f9;
            --date-box-bg: #111111;
            --date-box-text: #ffffff;
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
            --schedule-bg: #1e293b;
            --date-box-bg: #38bdf8;
            --date-box-text: #0f172a;
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

        /* --- STRUKTUR AGENDA JADWAL --- */
        .schedule-item { 
            display: flex; 
            align-items: center;
            gap: 20px; 
            padding: 18px; 
            border-left: 4px solid var(--date-box-bg); 
            background: var(--schedule-bg); 
            border-radius: 0 12px 12px 0; 
            margin-bottom: 16px; 
            border-top: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 6px var(--shadow);
            transition: transform 0.2s;
        }
        .schedule-item:hover { transform: translateX(3px); }
        
        .date-box { 
            background: var(--date-box-bg); 
            color: var(--date-box-text); 
            padding: 12px; 
            border-radius: 8px; 
            text-align: center; 
            font-weight: 700; 
            min-width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .date-box i { font-size: 1.3rem; }
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
                    <div class="menu-item active"><a href="fotografer-jadwal.php"><i class="fa-solid fa-calendar-days"></i> Jadwal</a></div>
                    <div class="menu-item"><a href="fotografer-pesanan.php"><i class="fa-solid fa-envelope"></i> Pesanan</a></div>
                    <div class="menu-item"><a href="fotografer-ulasan.php"><i class="fa-solid fa-star"></i> Ulasan</a></div>
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
                        <p>Kelola manajemen waktu pengerjaan proyek fotografi Anda di sini. Semua reservasi klien yang telah Anda setujui (*confirmed*) akan otomatis tercantum pada agenda kerja.</p>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Maison Work Calendar.</div>
                </div>

                <div class="right-panel">
                    <h3>Agenda Sesi Foto Mendatang</h3>
                    
                    <?php if(mysqli_num_rows($query_agenda) == 0): ?>
                        <div style="padding: 20px; text-align: center; color: var(--text-muted); border: 1px dashed var(--border-color); border-radius: 12px;">
                            <i class="fa-regular fa-calendar" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            Tidak ada agenda pemotretan terdekat dalam waktu dekat.
                        </div>
                    <?php endif; ?>

                    <?php while($ag = mysqli_fetch_assoc($query_agenda)): ?>
                        <div class="schedule-item">
                            <div class="date-box">
                                <i class="fa-regular fa-calendar-check"></i>
                            </div>
                            <div>
                                <strong style="font-size: 1.05rem; color: var(--text-main);">Sesi Foto: <?= htmlspecialchars($ag['nama']); ?></strong>
                                <p style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 0.9rem;">
                                    <?php if(isset($ag['booking_date'])): ?>
                                        <i class="fa-regular fa-clock" style="margin-right: 4px;"></i> <?= date('d M Y', strtotime($ag['booking_date'])); ?> 
                                        <?= isset($ag['booking_time']) ? ' - ' . htmlspecialchars($ag['booking_time']) : ''; ?> |
                                    <?php endif; ?>
                                    Status: <span style="color: #22c55e; font-weight: 600;">Confirmed / Aktif</span>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
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