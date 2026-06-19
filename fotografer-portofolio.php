<?php
session_start();
include 'koneksi.php';

// ... (Proteksi login Anda) ...

$id_user = $_SESSION['id_user'];
$pesan = "";

// Mengambil ID Fotografer (Pastikan nama kolom sudah 'id_fotografer')
$q_photo = mysqli_query($koneksi, "SELECT id_fotografer FROM photographers WHERE id_user = '$id_user'");
$d_photo = mysqli_fetch_assoc($q_photo);
$id_photo = $d_photo['id_fotografer'] ?? 0;

// --- POSISI KODE PROSES UPLOAD DI SINI ---
if (isset($_POST['upload_portfolio'])) {
    // GANTI: id_kategori menjadi id_paket
    $title = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $id_paket = intval($_POST['id_paket']);
    
    if ($_FILES['portfolio_file']['name'] != '') {
        $target_dir = "assets/img/portfolio/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['portfolio_file']['name'], PATHINFO_EXTENSION);
        $foto_nama = "port_" . $id_photo . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $foto_nama;
        
        if (move_uploaded_file($_FILES['portfolio_file']['tmp_name'], $target_file)) {
            $tanggal_hari_ini = date('Y-m-d');
            $deskripsi_default = "Karya portofolio " . $title; 
            
            // GANTI: id_kategori menjadi id_paket di dalam query
            $insert = mysqli_query($koneksi, "INSERT INTO portofolio (id_fotografer, id_paket, judul, gambar, deskripsi, tanggal_upload) 
                                              VALUES ('$id_photo', '$id_paket', '$title', '$foto_nama', '$deskripsi_default', '$tanggal_hari_ini')") 
                      or die(mysqli_error($koneksi)); 
            
            if ($insert) {
                $pesan = "<div class='alert alert-success'>Berhasil diupload!</div>";
            }
        }
    }
}

// Ambil data portofolio dari DB untuk ditampilkan di galeri bawah
$portfolio_list = [];
// Query ini sudah mencakup data portofolio DAN nama kategorinya
$query = "SELECT p.*, pk.package_name 
          FROM portofolio p 
          LEFT JOIN packages pk ON p.id_paket = pk.id_package 
          WHERE p.id_fotografer = '$id_photo' 
          ORDER BY p.id_portofolio DESC LIMIT 6";

$q_gallery = mysqli_query($koneksi, $query);
if ($q_gallery) {
    while ($row = mysqli_fetch_assoc($q_gallery)) {
        // Kita simpan seluruh data baris ke dalam array
        $portfolio_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio Fotografer - Maison Étoira</title>
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
            --input-bg: #ffffff;
            --btn-bg: #111111;
            --btn-text: #ffffff;
            --panel-left-bg: #111111;
            --panel-left-text: #ffffff;
            --gallery-empty: #eeeeee;
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
            --input-bg: #0f172a;
            --btn-bg: #38bdf8;
            --btn-text: #0f172a;
            --panel-left-bg: #1e293b;
            --panel-left-text: #f8fafc;
            --gallery-empty: #334155;
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

        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-main); }
        
        .form-control { 
            width: 100%; padding: 13px 16px;
            background: var(--input-bg); color: var(--text-main);
            border: 1px solid var(--border-color); border-radius: 8px;
            box-sizing: border-box; font-size: 0.95rem; font-family: inherit;
            transition: border-color 0.2s, background 0.3s;
        }
        .form-control:focus { border-color: var(--text-main); outline: none; }

        button[type="submit"] { 
            background: var(--btn-bg); color: var(--btn-text); padding: 14px; border: none; border-radius: 8px; 
            font-weight: 600; font-size: 0.95rem; cursor: pointer; width: 100%; font-family: inherit;
            transition: opacity 0.2s, background 0.3s;
        }
        button[type="submit"]:hover { opacity: 0.9; }

        .alert { padding: 14px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 22px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .alert-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        /* --- STRUKTUR GALERI --- */
        .gallery-placeholder { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 16px; margin-top: 20px; }
        
        .gallery-item { 
            aspect-ratio: 1; background: var(--gallery-empty); border-radius: 12px; 
            display: flex; flex-direction: column; align-items: center; justify-content: center; 
            color: var(--text-muted); border: 2px dashed var(--border-color); overflow: hidden; position: relative;
        }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; }
        
        .gallery-title-overlay {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: rgba(0,0,0,0.6); color: #fff; padding: 6px;
            font-size: 0.75rem; text-align: center; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;
        }
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
                    <div class="menu-item active"><a href="fotografer-portofolio.php"><i class="fa-solid fa-images"></i> Portofolio</a></div>
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
            <div class="profile-wrapper">
                
                <div class="left-panel">
                    <div>
                        <h2>Maison Étoira</h2>
                        <p>Pamerkan jepretan terbaik Anda dengan sentuhan estetika tinggi (seperti format sinematik 35mm atau telefoto). Portofolio yang terkurasi baik adalah magnet utama pemikat calon klien.</p>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Maison Portfolio Management.</div>
                </div>

                <div class="right-panel">
                    <h3>Manajemen Galeri Portofolio</h3>
                    <?= $pesan; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Judul Album</label>
                            <input type="text" name="judul" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Pilih Kategori</label>
                            <select name="id_paket" class="form-control" required>
                                <option value="">-- Pilih Kategori Paket --</option>
                                <?php
                                // Pastikan nama tabel dan kolom sesuai dengan yang ada di database Anda
                                $q_pak = mysqli_query($koneksi, "SELECT id_package, package_name FROM packages");
                                
                                if ($q_pak && mysqli_num_rows($q_pak) > 0) {
                                    while ($pak = mysqli_fetch_assoc($q_pak)) {
                                        echo "<option value='".$pak['id_package']."'>".$pak['package_name']."</option>";
                                    }
                                } else {
                                    echo "<option value=''>Tidak ada paket tersedia</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Pilih File Foto</label>
                            <input type="file" name="portfolio_file" accept="image/*" required>
                        </div>
                        <button type="submit" name="upload_portfolio">Upload ke Galeri</button>
                    </form>

                    <h4 style="margin-top: 40px; margin-bottom: 10px; font-size: 1.1rem; font-weight: 700;">Koleksi Foto Anda saat ini:</h4>
                    <div class="gallery-placeholder">
                        <?php if (!empty($portfolio_list)): ?>
                            <?php foreach ($portfolio_list as $item): ?>
                                <div class="gallery-item" style="position: relative;">
                                    <img src="assets/img/portfolio/<?= htmlspecialchars($item['gambar']); ?>" alt="<?= htmlspecialchars($item['judul']); ?>">
                                    
                                    <div class="gallery-title-overlay">
                                        <?= htmlspecialchars($item['judul']); ?>
                                        <br>
                                        <span style="font-size: 0.8em; color: #f0f0f0;">Paket: <?= htmlspecialchars($item['nama_paket'] ?? 'Umum'); ?></span>
                                    </div>
                                    
                                    <a href="hapus-portofolio.php?id=<?= $item['id_portofolio']; ?>" 
                                    onclick="return confirm('Yakin ingin menghapus karya ini?')" 
                                    style="position: absolute; top: 10px; right: 10px; background: red; color: white; padding: 5px 10px; text-decoration: none; border-radius: 5px;">
                                    Hapus
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="gallery-item"><i class="fa-regular fa-image" style="font-size: 1.5rem;"></i></div>
                            <div class="gallery-item"><i class="fa-regular fa-image" style="font-size: 1.5rem;"></i></div>
                            <div class="gallery-item"><i class="fa-regular fa-image" style="font-size: 1.5rem;"></i></div>
                        <?php endif; ?>
                    </div>
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