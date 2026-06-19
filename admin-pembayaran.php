<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit();
}

// Logika Update Status (Terima/Tolak)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = ($_GET['action'] == 'terima') ? 'confirmed' : 'rejected';
    mysqli_query($koneksi, "UPDATE bookings SET status = '$status' WHERE id_booking = '$id'");
    header("Location: admin-pembayaran.php");
    exit();
}

// Logika Filter Status
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$where_clause = "";

if ($filter == 'menunggu') {
    $where_clause = "WHERE b.status = 'pending' OR b.status = 'unpaid'";
} elseif ($filter == 'diterima') {
    $where_clause = "WHERE b.status = 'confirmed'";
} elseif ($filter == 'ditolak') {
    $where_clause = "WHERE b.status = 'rejected'";
}

$query_sql = "SELECT b.*, u.nama as pelanggan, u_photo.nama as fotografer, pk.package_name, port.price 
              FROM bookings b 
              JOIN users u ON b.id_customer = u.id_user 
              JOIN photographers ph ON b.id_fotografer = ph.id_fotografer 
              JOIN users u_photo ON ph.id_user = u_photo.id_user
              JOIN portofolio port ON b.id_portofolio = port.id_portofolio 
              JOIN packages pk ON port.id_paket = pk.id_package 
              $where_clause 
              ORDER BY b.id_booking DESC";

$query = mysqli_query($koneksi, $query_sql) or die(mysqli_error($koneksi));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - Admin Maison Étoira</title>
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
            --tab-active-bg: #111111;
            --tab-active-text: #ffffff;
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
            --tab-active-bg: #38bdf8;
            --tab-active-text: #0f172a;
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
        
        /* Efek Timbul saat Hover */
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
        
        /* Menu yang sedang Aktif */
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
        
        .main-content h2 {
            margin-top: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
        }
        
        /* --- CARD PANEL TABEL --- */
        .card-panel {
            background: var(--bg-card); 
            border: 1px solid var(--border-color);
            border-radius: 16px; 
            padding: 30px; 
            box-shadow: 0 4px 24px var(--shadow);
            margin-top: 25px;
            transition: background 0.3s, border-color 0.3s;
        }

        /* --- TAB FILTER --- */
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; }
        .tab-btn { padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.9rem; color: var(--text-muted); border: 1px solid var(--border-color); transition: 0.2s; }
        .tab-btn.active { background: var(--tab-active-bg); color: var(--tab-active-text); border-color: var(--tab-active-bg); font-weight: 600; }
        
        /* --- TABEL --- */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: var(--text-main); }
        th { background-color: var(--bg-base); color: var(--text-main); font-weight: 600; }
        tr:hover td { background-color: rgba(0, 0, 0, 0.01); }
        body.dark-mode tr:hover td { background-color: rgba(255, 255, 255, 0.02); }

        /* Thumbnail Bukti Transfer */
        .proof-img { width: 45px; height: 35px; background: var(--bg-base); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; border-radius: 6px; }
        
        /* Tombol Aksi */
        .btn-action { padding: 6px 10px; border-radius: 6px; text-decoration: none; border: 1px solid var(--border-color); display: inline-block; transition: 0.2s; background: var(--bg-card); }
        .btn-approve { color: #22c55e; border-color: rgba(34, 197, 94, 0.3); }
        .btn-reject { color: #ef4444; border-color: rgba(239, 68, 68, 0.3); }
        .btn-action:hover { opacity: 0.8; background: var(--bg-base); }

        /* Status Badges */
        .status { font-weight: 600; font-size: 0.85rem; }
        .status-pending { color: #eab308; }
        .status-confirmed { color: #22c55e; }
        .status-rejected { color: #ef4444; }
    </style>
</head>
<body>

    <div class="admin-container">
        <aside class="sidebar">
            <div>
                <div class="sidebar-brand">Maison Étoira</div>
                
                <ul class="menu-list">
                    <li class="menu-item"><a href="admin-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                    <li class="menu-item"><a href="admin-users.php"><i class="fa-solid fa-users"></i> Data User</a></li>
                    <li class="menu-item"><a href="admin-fotografer.php"><i class="fa-solid fa-camera"></i> Fotografer</a></li>
                    <li class="menu-item"><a href="admin-paket.php"><i class="fa-solid fa-box-open"></i> Paket Layanan</a></li>
                    <li class="menu-item active"><a href="admin-pembayaran.php"><i class="fa-solid fa-credit-card"></i> Pembayaran</a></li>
                    <li class="menu-item"><a href="admin-laporan.php"><i class="fa-solid fa-file-invoice-dollar"></i> Laporan</a></li>
                    <li class="menu-item"><a href="admin-pengaturan.php"><i class="fa-solid fa-sliders"></i> Pengaturan</a></li>
                </ul>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <button class="dark-mode-toggle" id="darkModeBtn">
                    <i class="fa-regular fa-moon"></i> <span>Mode Gelap</span>
                </button>
                <ul class="menu-list">
                    <li class="menu-item logout"><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
                </ul>
            </div>
        </aside>

        <main class="main-content">
            <h2>Verifikasi Pembayaran</h2>
            <p style="color: var(--text-muted); margin: 5px 0 0 0; font-size: 0.95rem;">Tinjau bukti transaksi pembayaran transfer bank masuk dari pelanggan.</p>

            <div class="card-panel">
                <div class="filter-tabs">
                    <a href="?filter=semua" class="tab-btn <?= ($filter == 'semua') ? 'active' : ''; ?>">Semua</a>
                    <a href="?filter=menunggu" class="tab-btn <?= ($filter == 'menunggu') ? 'active' : ''; ?>">Menunggu Verifikasi</a>
                    <a href="?filter=diterima" class="tab-btn <?= ($filter == 'diterima') ? 'active' : ''; ?>">Diterima</a>
                    <a href="?filter=ditolak" class="tab-btn <?= ($filter == 'ditolak') ? 'active' : ''; ?>">Ditolak</a>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Pelanggan</th>
                            <th>Fotografer</th>
                            <th>Total</th>
                            <th>Bukti Transfer</th>
                            <th>Status</th>
                            <th style="text-align: center; width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while($row = mysqli_fetch_assoc($query)) : 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><strong><?= htmlspecialchars($row['pelanggan']); ?></strong></td>
                            <td><?= htmlspecialchars($row['fotografer']); ?></td>
                            <td><strong>Rp <?= number_format($row['price'], 0, ',', '.'); ?></strong></td>
                            <td>
                                <div class="proof-img">
                                    <i class="fa-regular fa-image" style="color: var(--text-muted);"></i>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    if($row['status'] == 'pending' || $row['status'] == 'unpaid') echo '<span class="status status-pending"><i class="fa-regular fa-clock"></i> Menunggu Verifikasi</span>';
                                    elseif($row['status'] == 'confirmed') echo '<span class="status status-confirmed"><i class="fa-regular fa-circle-check"></i> Diterima</span>';
                                    else echo '<span class="status status-rejected"><i class="fa-regular fa-circle-xmark"></i> Ditolak</span>';
                                ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($row['status'] == 'pending' || $row['status'] == 'unpaid') : ?>
                                    <a href="?action=terima&id=<?= $row['id_booking']; ?>" class="btn-action btn-approve" title="Terima"><i class="fa-solid fa-check"></i></a>
                                    <a href="?action=tolak&id=<?= $row['id_booking']; ?>" class="btn-action btn-reject" title="Tolak"><i class="fa-solid fa-xmark"></i></a>
                                <?php else : ?>
                                    <span style="color: var(--text-muted); font-size: 0.9rem;">-</span>
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