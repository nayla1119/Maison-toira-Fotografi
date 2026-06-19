<?php
// 1. AKTIFKAN SESSION PHP
session_start();

// 2. HUBUNGKAN DATABASE
include 'koneksi.php';

$pesan = "";

// Jika pengguna sudah dalam posisi login, langsung lempar kembali ke Beranda
if (isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit();
}

// 3. PROSES KETIKA TOMBOL DAFTAR DIKLIK
if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $role_pilihan = $_POST['role']; // Mengambil data 'pelanggan' atau 'fotografer' dari input hidden

    // Cek apakah email sudah terdaftar sebelumnya
    $cek_email = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($cek_email) > 0) {
        $pesan = "<div class='alert alert-danger'>Email sudah terdaftar! Silakan gunakan email lain.</div>";
    } elseif ($password !== $konfirmasi_password) {
        $pesan = "<div class='alert alert-danger'>Konfirmasi password tidak cocok!</div>";
    } else {
        // Enkripsi password menggunakan bcrypt aman
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Query insert data ke tabel users sesuai role pilihan
        $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password_hash', '$role_pilihan')";
        
        if (mysqli_query($koneksi, $query)) {
            // Jika berhasil, kirim pesan sukses lewat session lalu lempar ke login.php
            $_SESSION['sukses_register'] = "Pendaftaran akun sebagai " . ucfirst($role_pilihan) . " berhasil! Silakan masuk.";
            header("Location: login.php");
            exit();
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal mendaftarkan akun. Silakan coba lagi!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Maison Étoira</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <style>
        .auth-container { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 120px 5% 60px 5%; }
        .auth-card { background: var(--bg-card, #fff); border: 1px solid var(--border-color, #eee); padding: 40px; border-radius: 24px; width: 100%; max-width: 450px; box-shadow: 0 10px 30px var(--shadow-color); }
        .auth-card h2 { font-family: 'Playfair Display', serif; margin-bottom: 8px; font-size: 2rem; color: var(--text-primary); }
        
        /* --- DESIGN SERUPA: ROLE SWITCHER TOGGLE --- */
        .role-switcher {
            display: flex; 
            background: var(--bg-secondary, #f8fafc); 
            padding: 5px; 
            border-radius: 14px;
            margin: 20px 0; 
            border: 1px solid var(--border-color, #e2e8f0);
        }
        .role-btn {
            flex: 1; 
            padding: 12px; 
            border: none; 
            background: none; 
            border-radius: 10px;
            font-weight: 600; 
            font-size: 0.9rem; 
            cursor: pointer; 
            color: var(--text-secondary, #64748b); 
            transition: all var(--transition-speed, 0.3s);
        }
        .role-btn.active { 
            background: var(--accent-blue, #121a24); 
            color: #ffffff; 
            box-shadow: 0 4px 12px var(--shadow-color); 
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-primary); }
        
        .form-control { width: 100%; padding: 14px 18px; border: 1px solid var(--border-color, #ddd); border-radius: 12px; background: var(--bg-secondary, transparent); color: var(--text-primary); font-family: inherit; font-size: 0.95rem; box-sizing: border-box; transition: var(--transition-speed, 0.3s); }
        .form-control:focus { outline: none; border-color: var(--accent-blue, #121a24); }
        
        .btn-submit { width: 100%; padding: 16px; margin-top: 10px; border: none; border-radius: 12px; background: var(--accent-blue, #121a24); color: #fff; font-weight: 700; font-size: 1rem; cursor: pointer; transition: var(--transition-speed, 0.3s); }
        .btn-submit:hover { background: var(--accent-hover, #1f2937); transform: translateY(-1px); }

        .alert { padding: 12px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; line-height: 1.4; }
        .alert-danger { background: #ffebeb; color: #ad2a2a; border: 1px solid #fcc; }
    </style>
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <main class="auth-container">
        <div class="auth-card">
            <h2>Buat Akun Baru</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 25px;">Lengkapi data diri Anda untuk membuat akun baru.</p>
            
            <?php echo $pesan; ?>

            <form action="" method="POST">
                <div class="role-switcher">
                    <button type="button" class="role-btn active" id="btnPelanggan">Pelanggan</button>
                    <button type="button" class="role-btn" id="btnFotografer">Fotografer</button>
                </div>
                <input type="hidden" name="role" id="roleInput" value="pelanggan">

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap Anda" required>
                </div>

                <div class="form-group">
                    <label>Alamat Email</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Buat kata sandi baru" required>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi kata sandi Anda" required>
                </div>
                
                <button type="submit" name="register" class="btn-submit">Daftar Akun</button>
            </form>

            <div style="text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-secondary);">
                Sudah punya akun? <a href="login.php" style="color: var(--accent-blue); font-weight: 600; text-decoration: none;">Login di sini</a>
            </div>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>

    <script>
        const btnPelanggan = document.getElementById('btnPelanggan');
        const btnFotografer = document.getElementById('btnFotografer');
        const roleInput = document.getElementById('roleInput');

        btnPelanggan.addEventListener('click', () => {
            btnPelanggan.classList.add('active');
            btnFotografer.classList.remove('active');
            roleInput.value = 'pelanggan';
        });

        btnFotografer.addEventListener('click', () => {
            btnFotografer.classList.add('active');
            btnPelanggan.classList.remove('active');
            roleInput.value = 'fotografer';
        });
    </script>
    <script src="assets/js/script.js"></script>
</body>
</html>