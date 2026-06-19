<?php
session_start();
include 'koneksi.php';

// Proteksi: Hanya Pelanggan/Customer yang sudah login yang bisa melakukan booking
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_customer = $_SESSION['id_user'];
$pesan = "";

// 1. AMBIL DATA FOTOGRAFER UNTUK DROPDOWN
$query_fotografer = mysqli_query($koneksi, "SELECT ph.id_fotografer, u.nama FROM photographers ph JOIN users u ON ph.id_user = u.id_user");

// 2. AMBIL DATA PAKET, HARGA, DAN NAMA FOTOGRAFER DARI PORTOFOLIO
$query_paket = mysqli_query($koneksi, "SELECT p.id_portofolio, pk.package_name, p.price, u.nama as nama_fotografer 
                                       FROM portofolio p
                                       JOIN packages pk ON p.id_paket = pk.id_package
                                       JOIN photographers ph ON p.id_fotografer = ph.id_fotografer
                                       JOIN users u ON ph.id_user = u.id_user");

// 3. PROSES SIMPAN FORM BOOKING JASA
if (isset($_POST['konfirmasi_booking'])) {
    // Pastikan semua variabel sudah dibersihkan sebelum masuk ke query
    $id_customer   = intval($_SESSION['id_user']); // Gunakan intval untuk angka
    $id_portofolio = intval($_POST['id_portofolio']); // Gunakan intval untuk angka
    $tanggal       = mysqli_real_escape_string($koneksi, $_POST['tanggal_pemotretan']);
    $lokasi        = mysqli_real_escape_string($koneksi, $_POST['lokasi_acara']);
    $catatan       = mysqli_real_escape_string($koneksi, $_POST['catatan_tambahan']);
    $status        = "pending";

    $query_insert = "INSERT INTO bookings (id_customer, id_portofolio, created_at, lokasi, catatan, status) 
                    VALUES ('$id_customer', '$id_portofolio', '$tanggal', '$lokasi', '$catatan', '$status')";

    if (mysqli_query($koneksi, $query_insert)) {
        $pesan = "<div class='alert alert-success'>Booking berhasil diajukan! Menunggu konfirmasi admin.</div>";
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Jasa - Maison Étoira</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: var(--bg-secondary, #f9f9f9);
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* --- NAVBAR STYLE (SESUAI WIREFRAME) --- */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 15px 50px;
            border-bottom: 1px solid #eee;
        }
        .navbar-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #000;
            text-decoration: none;
        }
        .navbar-menu {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .navbar-menu a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            font-size: 0.95rem;
            transition: 0.3s;
        }
        .navbar-menu a:hover { color: #000; }
        .navbar-profile { font-size: 1.3rem; color: #333; }

        /* --- FORM LAYOUT CENTERED --- */
        .booking-container {
            display: flex;
            justify-content: center;
            padding: 50px 20px;
        }
        .booking-card {
            background-color: #top;
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .booking-card h2 {
            font-size: 1.6rem;
            color: #111;
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
            color: #111;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #111;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* --- BUTTON KONFIRMASI HITAM WIREFRAME --- */
        .btn-booking {
            width: 100%;
            padding: 14px;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-booking:hover {
            background: #333;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        .alert-success { background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

    <header class="navbar">
        <a href="index.php" class="navbar-logo">LOGO</a>
        <nav>
            <ul class="navbar-menu">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="fotografer.php">Fotografer</a></li>
                <li><a href="paket.php">Paket</a></li>
            </ul>
        </nav>
        <a href="dashboard.php" class="navbar-profile"><i class="fa-regular fa-user"></i></a>
    </header>

    <main class="booking-container">
        <div class="booking-card">
            <h2>Form Pemesanan</h2>
            
            <?php echo $pesan; ?>

            <form action="" method="POST">
                
                <div class="form-group">
                    <label>Fotografer</label>
                    <select name="id_fotografer" class="form-control" required>
                        <option value="">-- Pilih Fotografer --</option>
                        <?php while($ft = mysqli_fetch_assoc($query_fotografer)) : ?>
                            <option value="<?php echo $ft['id_fotografer']; ?>"><?php echo htmlspecialchars($ft['nama']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Pilih Paket Layanan</label>
                    <select name="id_portofolio" class="form-control" required>
                        <option value="">-- Pilih Paket & Harga --</option>
                        <?php while($pk = mysqli_fetch_assoc($query_paket)) : ?>
                            <option value="<?php echo $pk['id_portofolio']; ?>">
                                <?php echo htmlspecialchars($pk['package_name']) . " (" . $pk['nama_fotografer'] . ") - Rp " . number_format($pk['price'], 0, ',', '.'); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tanggal Pemotretan</label>
                    <input type="date" name="tanggal_pemotretan" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Lokasi Acara</label>
                    <input type="text" name="lokasi_acara" class="form-control" placeholder="Masukkan lokasi acara" required>
                </div>

                <div class="form-group">
                    <label>Catatan Tambahan</label>
                    <textarea name="catatan_tambahan" class="form-control" placeholder="Tulis catatan tambahan (opsional)"></textarea>
                </div>

                <button type="submit" name="konfirmasi_booking" class="btn-booking">Konfirmasi Booking</button>
            </form>
        </div>
    </main>

</body>
</html>