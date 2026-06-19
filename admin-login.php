<?php
session_start();
include 'koneksi.php';

$pesan = "";

if (isset($_POST['admin_login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Menyesuaikan role dengan data asli di database kamu ('admin' & 'fotografer')
    $query = "SELECT * FROM users WHERE email = '$email' AND role IN ('admin', 'fotografer')";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            // Mendaftarkan session global untuk sistem pengaman dashboard
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['name']    = $row['nama']; 
            $_SESSION['role']    = $row['role']; 

            // Memisahkan rute halaman sesuai role masing-masing
            if ($row['role'] === 'admin') {
                header("Location: admin-dashboard.php");
                exit();
            } elseif ($row['role'] === 'fotografer') {
                header("Location: fotografer-dashboard.php");
                exit();
            }
        } else {
            $pesan = "<div class='alert alert-danger'>Kata sandi salah!</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger'>Akses ditolak! Akun Anda bukan Admin/Fotografer.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pintu Masuk Mitra - Maison Étoira</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #111; color: #fff; }
        .auth-card { background: #1a1a1a; border: 1px solid #333; padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; color: #aaa; }
        .form-control { width: 100%; padding: 12px 16px; border: 1px solid #333; border-radius: 8px; background: #222; color: #fff; box-sizing: border-box; }
        .form-control:focus { outline: none; border-color: #fff; }
        .alert { padding: 12px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; background: #5a1818; color: #ffb3b3; border: 1px solid #8a2525; }
    </style>
</head>
<body class="dark-mode">
    <main class="auth-container">
        <div class="auth-card">
            <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 5px; font-size: 1.8rem;">Maison Partner</h2>
            <p style="color: #666; font-size: 0.85rem; margin-bottom: 25px;">Pintu masuk khusus Admin Maison Étoira.</p>
            
            <?php echo $pesan; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Email Internal</label>
                    <input type="email" name="email" class="form-control" placeholder="nama@maison.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" name="admin_login" class="btn btn-primary" style="width: 100%; padding: 14px; background: #fff; color: #000; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Masuk ke Panel</button>
            </form>
        </div>
    </main>
</body>
</html>