<?php
session_start();
include 'koneksi.php';

// Proteksi: Hanya Fotografer yang bisa masuk
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'fotografer') {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// 1. Ambil ID Fotografer berdasarkan User ID yang login
$query_ft = mysqli_query($koneksi, "SELECT id_fotografer FROM photographers WHERE id_user = '$id_user'");
$data_ft = mysqli_fetch_assoc($query_ft);

// PENGAMAN: Jika user ini belum terdaftar di tabel photographers, set id_fotografer jadi 0
$id_fotografer = isset($data_ft['id_fotografer']) ? $data_ft['id_fotografer'] : 0;

// 2. Query Ringkasan (Stats)
// Pesanan Baru (Status: pending)
$count_new = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_booking FROM bookings WHERE id_fotografer = '$id_fotografer' AND status = 'pending'"));

// Total Pesanan
$count_total = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_booking FROM bookings WHERE id_fotografer = '$id_fotografer'"));

// Jadwal Hari Ini
$today = date('Y-m-d');
$count_today = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_booking FROM bookings WHERE id_fotografer = '$id_fotografer' AND DATE(created_at) = '$today'"));

// Total Pendapatan Fotografer
$query_income = mysqli_query($koneksi, "SELECT COUNT(p.id_package) as total FROM bookings b JOIN packages p ON b.id_package = p.id_package WHERE b.id_fotografer = '$id_fotografer' AND b.status = 'completed'");
$income_data = mysqli_fetch_assoc($query_income);
$total_income = $income_data['total'] ?? 0;

// 3. Query Pesanan Terbaru (Limit 5)
$query_recent = "SELECT b.*, u.nama AS nama_pelanggan, p.package_name 
                 FROM bookings b 
                 JOIN users u ON b.id_customer = u.id_user 
                 JOIN packages p ON b.id_package = p.id_package 
                 WHERE b.id_fotografer = '$id_fotografer' 
                 ORDER BY b.created_at DESC LIMIT 5";
$recent_bookings = mysqli_query($koneksi, $query_recent);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Fotografer - Maison Étoira</title>
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
            --badge-pending-bg: #fff7ed;
            --badge-pending-text: #9a3412;
            --badge-confirmed-bg: #eff6ff;
            --badge-confirmed-text: #1e40af;
            --badge-completed-bg: #f0fdf4;
            --badge-completed-text: #166534;
            --badge-unpaid-bg: #fef2f2;
            --badge-unpaid-text: #991b1b;
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
            --badge-pending-bg: rgba(251, 146, 60, 0.15);
            --badge-pending-text: #fdba74;
            --badge-confirmed-bg: rgba(96, 165, 250, 0.15);
            --badge-confirmed-text: #93c5fd;
            --badge-completed-bg: rgba(74, 222, 128, 0.15);
            --badge-completed-text: #86efac;
            --badge-unpaid-bg: rgba(248, 113, 113, 0.15);
            --badge-unpaid-text: #fca5a5;
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
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 18px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 10px;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        
        /* Efek Hover Timbul */
        .menu-item.active a, 
        .menu-item a:hover,
        .dark-mode-toggle:hover { 
            background: var(--hover-bg); 
            color: var(--text-main); 
            font-weight: 600;
            border-color: var(--border-color);
            box-shadow: 0 4px 12px var(--shadow-hover);
            transform: translateY(-1px);
        }
        
        .menu-item.active a {
            background: var(--bg-base);
            border-color: transparent;
            box-shadow: none;
            transform: none;
        }

        .menu-item.logout a { color: #ef4444; }
        .menu-item.logout a:hover { background: #fef2f2; border-color: #fee2e2; color: #ef4444; box-shadow: none; transform: none; }
        body.dark-mode .menu-item.logout a:hover { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); }
        
        .sidebar i { width: 20px; font-size: 1.1rem; text-align: center; }

        /* --- KONTEN UTAMA --- */
        .main-content { 
            margin-left: 280px; 
            flex-grow: 1; 
            padding: 50px 50px 50px 30px; 
        }
        
        .welcome-text h1 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
        }

        /* --- STATS GRID --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 35px;
            margin-top: 25px;
        }

        .stat-card {
            background-color: var(--bg-card);
            padding: 25px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: 8px;
            box-shadow: 0 4px 20px var(--shadow);
            transition: background 0.3s, border-color 0.3s;
        }

        .stat-card i {
            font-size: 1.3rem;
            color: var(--text-main);
            background: var(--bg-base);
            width: 46px; height: 46px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 10px;
            margin-bottom: 4px;
            transition: background 0.3s;
        }

        .stat-card span { color: var(--text-muted); font-size: 0.85rem; font-weight: 500; }
        .stat-card h2 { font-size: 1.6rem; color: var(--text-main); margin: 0; font-weight: 700; }

        /* --- PANEL TABEL --- */
        .table-container {
            background-color: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 30px;
            box-shadow: 0 4px 24px var(--shadow);
            transition: background 0.3s, border-color 0.3s;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .table-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; color: var(--text-main); }

        .btn-view-all {
            padding: 8px 16px;
            background: var(--bg-base);
            color: var(--text-main);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }
        .btn-view-all:hover {
            background: var(--text-main);
            color: var(--bg-card);
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: var(--text-main); }
        th { background-color: var(--bg-base); color: var(--text-main); font-weight: 600; }
        tr:hover td { background-color: rgba(0, 0, 0, 0.01); }
        body.dark-mode tr:hover td { background-color: rgba(255, 255, 255, 0.02); }

        /* --- BADGE STATUS --- */
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: var(--badge-pending-bg); color: var(--badge-pending-text); }
        .status-confirmed { background: var(--badge-confirmed-bg); color: var(--badge-confirmed-text); }
        .status-completed { background: var(--badge-completed-bg); color: var(--badge-completed-text); }
        .status-unpaid { background: var(--badge-unpaid-bg); color: var(--badge-unpaid-text); }
    </style>
</head>
<body>

    <div class="admin-container">
        <aside class="sidebar">
            <div>
                <div class="sidebar-brand">Maison Étoira</div>
                
                <nav class="menu-list">
                    <div class="menu-item active"><a href="fotografer-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></div>
                    <div class="menu-item"><a href="fotografer-profil.php"><i class="fa-solid fa-user"></i> Profil</a></div>
                    <div class="menu-item"><a href="fotografer-portofolio.php"><i class="fa-solid fa-images"></i> Portofolio</a></div>
                    <div class="menu-item"><a href="fotografer-paket.php"><i class="fa-solid fa-box"></i> Paket Layanan</a></div>
                    <div class="menu-item"><a href="fotografer-jadwal.php"><i class="fa-solid fa-calendar-days"></i> Jadwal</a></div>
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
            <div class="welcome-text">
                <h1>Selamat datang, <?= htmlspecialchars($_SESSION['name']); ?></h1>
                <p style="color: var(--text-muted); margin: 0; font-size: 0.95rem;">Berikut adalah ringkasan bisnis Anda hari ini.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fa-solid fa-inbox"></i>
                    <span>Pesanan Baru</span>
                    <h2><?= $count_new; ?></h2>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Total Pesanan</span>
                    <h2><?= $count_total; ?></h2>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Jadwal Hari Ini</span>
                    <h2><?= $count_today; ?></h2>
                </div>
                <div class="stat-card">
                    <i class="fa-solid fa-wallet"></i>
                    <span>Total Pendapatan</span>
                    <h2>Rp <?= number_format($total_income, 0, ',', '.'); ?></h2>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Pesanan Terbaru</h3>
                    <a href="fotografer-pesanan.php" class="btn-view-all">Lihat Semua</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Pelanggan</th>
                            <th>Paket</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($recent_bookings)) : ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nama_pelanggan']); ?></strong></td>
                            <td><?= htmlspecialchars($row['package_name']); ?></td>
                            <td><?= date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <span class="status-badge status-<?= $row['status']; ?>">
                                    <?php 
                                        if($row['status'] == 'pending') echo 'Menunggu Konfirmasi';
                                        else if($row['status'] == 'confirmed') echo 'Dikonfirmasi';
                                        else if($row['status'] == 'completed') echo 'Selesai';
                                        else if($row['status'] == 'unpaid') echo 'Menunggu Pembayaran';
                                    ?>
                                </span>
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