<?php
session_start();
include 'koneksi.php';

// Proses Tambah
if (isset($_POST['tambah_paket'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['package_name']);
    mysqli_query($koneksi, "INSERT INTO packages (package_name) VALUES ('$nama')");
    header("Location: admin-paket.php");
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM packages WHERE id_package = '$id'");
    header("Location: admin-paket.php");
}
// Proteksi Admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') { 
    header("Location: admin-login.php"); 
    exit(); 
}

$query = mysqli_query($koneksi, "SELECT * FROM packages");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Layanan - Admin Maison Étoira</title>
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
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: var(--text-main); }
        th { background-color: var(--bg-base); color: var(--text-main); font-weight: 600; }
        tr:hover td { background-color: rgba(0, 0, 0, 0.01); }
        body.dark-mode tr:hover td { background-color: rgba(255, 255, 255, 0.02); }
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
                    <li class="menu-item active"><a href="admin-paket.php"><i class="fa-solid fa-box-open"></i> Paket Layanan</a></li>
                    <li class="menu-item"><a href="admin-pembayaran.php"><i class="fa-solid fa-credit-card"></i> Pembayaran</a></li>
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
            <h2>Manajemen Paket Layanan</h2>
            <p style="color: var(--text-muted); margin: 5px 0 0 0; font-size: 0.95rem;">Kelola daftar paket pemotretan dan tarif layanan studio yang aktif.</p>
            <div class="card-panel" style="margin-bottom: 20px;">
                <h4 style="margin-top: 0;">Tambah Paket Baru</h4>
                <form method="POST" style="display: flex; gap: 10px;">
                    <input type="text" name="package_name" placeholder="Nama Paket" required style="padding: 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <button type="submit" name="tambah_paket" style="padding: 10px 20px; background: #111; color: #fff; border: none; border-radius: 8px; cursor: pointer;">Simpan</button>
                </form>
            </div>
            <div class="card-panel">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 100px;">ID Paket</th>
                            <th>Nama Paket</th>
                            <th>Aksi</th> </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($query)) : ?>
                        <tr>
                            <td><?= $row['id_package']; ?></td>
                            <td><strong><?= htmlspecialchars($row['package_name']); ?></strong></td>
                            
                            <td>
                                <a href="admin-paket.php?hapus=<?= $row['id_package']; ?>" 
                                onclick="return confirm('Yakin ingin menghapus paket ini?')" 
                                style="color: #ef4444; text-decoration: none; font-size: 0.9rem;">
                                <i class="fa-solid fa-trash"></i> Hapus
                                </a>
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