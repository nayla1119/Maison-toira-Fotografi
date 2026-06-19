<?php
session_start();
include 'koneksi.php';

// Proteksi: Hanya Fotografer yang bisa masuk
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'fotografer') { 
    header("Location: login.php"); 
    exit(); 
}

$id_user = $_SESSION['id_user'];

// Cari id_fotografer berdasarkan id_user
$q_photo = mysqli_query($koneksi, "SELECT id_fotografer FROM photographers WHERE id_user = '$id_user'");
$d_photo = mysqli_fetch_assoc($q_photo);
$id_photo = $d_photo['id_fotografer'] ?? 0;

// Update status pemotretan menjadi selesai
if (isset($_GET['selesai'])) {
    $id_b = mysqli_real_escape_string($koneksi, $_GET['selesai']);
    mysqli_query($koneksi, "UPDATE bookings SET status = 'completed' WHERE id_booking = '$id_b' AND id_fotografer = '$id_photo'");
    header("Location: fotografer-pesanan.php");
    exit();
}

// Ambil riwayat atau pesanan aktif fotografer ini
$query = mysqli_query($koneksi, "SELECT b.*, u.nama as pelanggan, pk.package_name
                                FROM bookings b 
                                JOIN users u ON b.id_customer = u.id_user 
                                JOIN portofolio port ON b.id_portofolio = port.id_portofolio 
                                JOIN packages pk ON port.id_paket = pk.id_package 
                                WHERE b.id_fotografer = '$id_photo' 
                                ORDER BY b.id_booking DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Maison Étoira</title>
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
            --th-bg: #111111;
            --th-text: #ffffff;
            --td-text: #2d3748;
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
            --th-bg: #0f172a;
            --th-text: #f8fafc;
            --td-text: #cbd5e1;
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
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-section h2 { margin: 0; font-size: 1.6rem; font-weight: 700; }

        /* --- TABEL STRUKTUR --- */
        .table-card { 
            background: var(--bg-card); 
            border-radius: 14px; 
            border: 1px solid var(--border-color); 
            overflow: hidden; 
            box-shadow: 0 4px 20px var(--shadow); 
            transition: background 0.3s, border-color 0.3s;
        }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: var(--th-bg); color: var(--th-text); padding: 18px 20px; font-size: 0.9rem; font-weight: 600; transition: background 0.3s, color 0.3s; }
        td { padding: 18px 20px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: var(--td-text); transition: border-color 0.3s, color 0.3s; }
        tr:last-child td { border-bottom: none; }
        
        /* Badges */
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; text-transform: uppercase; }
        .status-confirmed { background: rgba(29, 78, 216, 0.1); color: #3b82f6; }
        .status-completed { background: rgba(22, 101, 52, 0.1); color: #22c55e; }
        .status-pending { background: rgba(180, 83, 9, 0.1); color: #f59e0b; }

        /* Tombol Selesai */
        .btn-action { 
            background: #10b981; color: #fff; padding: 8px 16px; text-decoration: none; 
            border-radius: 8px; font-size: 0.85rem; font-weight: 600; transition: 0.2s; 
            display: inline-flex; align-items: center; gap: 6px; border: none; cursor: pointer;
        }
        .btn-action:hover { background: #059669; box-shadow: 0 4px 12px rgba(16,185,129,0.2); }
        .empty-state { text-align: center; padding: 60px !important; color: var(--text-muted); }
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
                    <div class="menu-item active"><a href="fotografer-pesanan.php"><i class="fa-solid fa-envelope"></i> Pesanan</a></div>
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
            <div class="header-section">
                <h2>Daftar Pesanan Pemotretan</h2>
            </div>

            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Paket Layanan</th>
                            <th>Tanggal Booking</th>
                            <th>Status Operasional</th>
                            <th>Aksi Manajemen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query) == 0) : ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fa-regular fa-folder-open" style="font-size: 2.5rem; margin-bottom: 12px; display:block;"></i>
                                    Belum ada data pesanan masuk dalam sistem reservasi Anda saat ini.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php while($row = mysqli_fetch_assoc($query)) : ?>
                        <tr>
                            <td><strong style="color: var(--text-main);"><?= htmlspecialchars($row['pelanggan']); ?></strong></td>
                            <td><?= htmlspecialchars($row['package_name']); ?></td>
                            <td><i class="fa-regular fa-calendar" style="color: var(--text-muted); margin-right: 5px;"></i> <?= date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php 
                                    if($row['status'] == 'confirmed') echo '<span class="status-badge status-confirmed">Disetujui</span>';
                                    elseif($row['status'] == 'completed') echo '<span class="status-badge status-completed">Selesai</span>';
                                    else echo '<span class="status-badge status-pending">'.htmlspecialchars($row['status']).'</span>';
                                ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'confirmed') : ?>
                                    <a href="?selesai=<?= $row['id_booking']; ?>" class="btn-action" onclick="return confirm('Selesaikan sesi pemotretan ini?')">
                                        <i class="fa-solid fa-circle-check"></i> Selesai Kerja
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.9rem;">Tidak ada aksi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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