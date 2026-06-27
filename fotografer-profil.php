<?php
session_start();
include 'koneksi.php';

// Proteksi: Hanya Fotografer yang bisa masuk
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'fotografer') { 
    header("Location: login.php"); 
    exit(); 
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['name'] ?? 'Fotografer';
$pesan = "";

// Ambil data fotografer saat ini
$query = mysqli_query($koneksi, "SELECT * FROM photographers WHERE id_user = '$id_user'");
$data = mysqli_fetch_assoc($query);

if (isset($_POST['update_profil'])) {
    $location = mysqli_real_escape_string($koneksi, $_POST['location']);
    $experience = mysqli_real_escape_string($koneksi, $_POST['experience']);
    $description = mysqli_real_escape_string($koneksi, $_POST['description']);
    $specialization = mysqli_real_escape_string($koneksi, $_POST['specialization']);
    $instagram = mysqli_real_escape_string($koneksi, $_POST['instagram']);

    // Logika Upload Foto Profil
    $foto_nama = $data['profile_image'] ?? ''; 
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "assets/img/photographers/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $foto_nama = "photo_" . $id_user . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $foto_nama;
        
        // PENTING: Cek apakah file berhasil di-upload
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $pesan = "<div class='alert alert-danger'>Gagal mengunggah foto. Periksa izin folder (permissions).</div>";
        }
    }

    // Cek kolom tambahan untuk menghindari error SQL jika belum ada di database
    $fields = "location='$location', experience='$experience', description='$description', profile_image='$foto_nama'";
    
    $check_spec = mysqli_query($koneksi, "SHOW COLUMNS FROM photographers LIKE 'specialization'");
    if (mysqli_num_rows($check_spec) > 0) { $fields .= ", specialization='$specialization'"; }
    
    $check_ig = mysqli_query($koneksi, "SHOW COLUMNS FROM photographers LIKE 'instagram'");
    if (mysqli_num_rows($check_ig) > 0) { $fields .= ", instagram='$instagram'"; }

    $update = mysqli_query($koneksi, "UPDATE photographers SET $fields WHERE id_user='$id_user'");

    if ($update) {
        $pesan = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Profil profesional berhasil diperbarui!</div>";
        $query = mysqli_query($koneksi, "SELECT * FROM photographers WHERE id_user = '$id_user'");
        $data = mysqli_fetch_assoc($query);
    } else {
        // Ganti bagian else Anda dengan ini sementara untuk melihat error
        $pesan = "<div class='alert alert-danger'>Gagal: " . mysqli_error($koneksi) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Fotografer - Maison Étoira</title>
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

        /* Panel Info Kiri */
        .left-panel {
            flex: 1; min-width: 300px;
            background: var(--panel-left-bg); color: var(--panel-left-text);
            padding: 40px; border-radius: 16px;
            display: flex; flex-direction: column; justify-content: space-between;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }
        .intro-text h2 { font-size: 1.8rem; margin: 0 0 10px 0; font-weight: 700; }
        .intro-text p { color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 25px; }

        .avatar-preview {
            width: 110px; height: 110px; border-radius: 50%; background: #222;
            border: 3px solid var(--border-color); overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        .avatar-preview img { width: 100%; height: 100%; object-fit: cover; }

        /* Panel Form Kanan */
        .right-panel {
            flex: 2; min-width: 450px;
            background: var(--bg-card); padding: 40px; border-radius: 16px;
            border: 1px solid var(--border-color); box-shadow: 0 4px 24px var(--shadow);
            transition: background 0.3s, border-color 0.3s;
        }

        .form-group { margin-bottom: 22px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-main); }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); }
        
        .form-control { 
            width: 100%; padding: 13px 16px 13px 45px;
            background: var(--input-bg); color: var(--text-main);
            border: 1px solid var(--border-color); border-radius: 8px;
            box-sizing: border-box; font-size: 0.95rem; font-family: inherit;
            transition: border-color 0.2s, background 0.3s;
        }
        .form-control:focus { border-color: var(--text-main); outline: none; }
        textarea.form-control { padding-left: 16px; min-height: 110px; resize: vertical; }

        button[type="submit"] { 
            background: var(--btn-bg); color: var(--btn-text); padding: 14px; border: none; border-radius: 8px; 
            font-weight: 600; font-size: 0.95rem; cursor: pointer; width: 100%; font-family: inherit;
            transition: opacity 0.2s, background 0.3s, color 0.3s;
        }
        button[type="submit"]:hover { opacity: 0.9; }

        .alert { padding: 14px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 22px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .alert-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .alert-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    </style>
</head>
<body>

    <div class="admin-container">
        <aside class="sidebar">
            <div>
                <div class="sidebar-brand">Maison Étoira</div>
                
                <nav class="menu-list">
                    <div class="menu-item"><a href="fotografer-dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></div>
                    <div class="menu-item active"><a href="fotografer-profil.php"><i class="fa-solid fa-user"></i> Profil</a></div>
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
            <div class="profile-wrapper">
                
                <div class="left-panel">
                    <div class="intro-text">
                        <h2>Halo, <?= htmlspecialchars($nama_user); ?>!</h2>
                        <p>Data profil yang lengkap dan representatif akan meningkatkan peluang konfirmasi pesanan dari klien.</p>
                        
                        <div class="avatar-preview">
                            <?php if(!empty($data['profile_image'])): ?>
                                <img src="assets/img/photographers/<?= $data['profile_image']; ?>" alt="Foto Profil">
                            <?php else: ?>
                                <i class="fa-solid fa-user-tie" style="font-size: 2.5rem; color: var(--text-muted);"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Maison Partner Panel 2026.</div>
                </div>

                <div class="right-panel">
                    <?= $pesan; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <div class="form-group">
                            <label>Foto Profil Utama (Saran: Rasio 1:1 / Setengah Badan)</label>
                            <input type="file" name="profile_image" accept="image/*" style="font-size: 0.9rem; color: var(--text-main);">
                        </div>

                        <div class="form-group">
                            <label>Lokasi Domisili / Fokus Area</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-location-dot"></i>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($data['location'] ?? ''); ?>" placeholder="Contoh: Surakarta, Indonesia" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Pengalaman Kerja (Tahun / Jam Terbang)</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-briefcase"></i>
                                <input type="text" name="experience" class="form-control" value="<?= htmlspecialchars($data['experience'] ?? ''); ?>" placeholder="Contoh: 3 Tahun Aktif / 50+ Event" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Kategori Spesialisasi Utama</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-camera-retro"></i>
                                <select name="specialization" class="form-control">
                                    <?php $spec = $data['specialization'] ?? ''; ?>
                                    <option value="Wedding / Pre-Wedding" <?= $spec == 'Wedding / Pre-Wedding' ? 'selected' : ''; ?>>Wedding / Pre-Wedding</option>
                                    <option value="Studio Portrait & Graduation" <?= $spec == 'Studio Portrait & Graduation' ? 'selected' : ''; ?>>Studio Portrait & Graduation</option>
                                    <option value="Product & Fashion Commercial" <?= $spec == 'Product & Fashion Commercial' ? 'selected' : ''; ?>>Product & Fashion Commercial</option>
                                    <option value="Event & Concert Photography" <?= $spec == 'Event & Concert Photography' ? 'selected' : ''; ?>>Event & Concert Photography</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Username Instagram Portofolio</label>
                            <div class="input-wrapper">
                                <i class="fa-brands fa-instagram"></i>
                                <input type="text" name="instagram" class="form-control" value="<?= htmlspecialchars($data['instagram'] ?? ''); ?>" placeholder="Contoh: @maison.etoira">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Deskripsi Gaya Memotret & Bio Singkat</label>
                            <textarea name="description" class="form-control" placeholder="Ceritakan kelebihan alat, gear, dan tone warna andalan jepretanmu..." required><?= htmlspecialchars($data['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_profil">Simpan Perubahan Profil</button>
                    </form>
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