<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_customer = $_SESSION['id_user'];
$pesan = "";

// Query Data (tetap sama)
$query_fotografer = mysqli_query($koneksi, "SELECT ph.id_fotografer, u.nama FROM photographers ph JOIN users u ON ph.id_user = u.id_user");
$query_paket = mysqli_query($koneksi, "SELECT p.id_portofolio, pk.package_name, p.price, u.nama as nama_fotografer 
                                       FROM portofolio p
                                       JOIN packages pk ON p.id_paket = pk.id_package
                                       JOIN photographers ph ON p.id_fotografer = ph.id_fotografer
                                       JOIN users u ON ph.id_user = u.id_user");

if (isset($_POST['konfirmasi_booking'])) {
    $id_customer   = intval($_SESSION['id_user']);
    $id_portofolio = intval($_POST['id_portofolio']);
    $tanggal       = mysqli_real_escape_string($koneksi, $_POST['tanggal_pemotretan']);
    $lokasi        = mysqli_real_escape_string($koneksi, $_POST['lokasi_acara']);
    $catatan       = mysqli_real_escape_string($koneksi, $_POST['catatan_tambahan']);
    
    // --- TAMBAHKAN PROSES UPLOAD DI SINI ---
    $nama_file = $_FILES['bukti_transfer']['name'];
    $tmp_file  = $_FILES['bukti_transfer']['tmp_name'];
    $ekstensi  = pathinfo($nama_file, PATHINFO_EXTENSION);
    $nama_baru = "bukti_" . $id_customer . "_" . time() . "." . $ekstensi;
    $target_dir = "uploads/bukti_transfer/" . $nama_baru;

    if (move_uploaded_file($tmp_file, $target_dir)) {
        // --- AMBIL ID FOTOGRAFER ---
        $query_cari = mysqli_query($koneksi, "SELECT id_fotografer FROM portofolio WHERE id_portofolio = '$id_portofolio'");
        $data_foto  = mysqli_fetch_assoc($query_cari);
        
        if (!$data_foto) {
            $pesan = "<div class='alert alert-danger'>Error: Paket tidak ditemukan.</div>";
        } else {
            $id_fotografer = $data_foto['id_fotografer'];

            $query_insert = "INSERT INTO bookings (id_customer, id_portofolio, id_fotografer, created_at, location, note, status, bukti_transfer) 
                             VALUES ('$id_customer', '$id_portofolio', '$id_fotografer', '$tanggal', '$lokasi', '$catatan', 'pending', '$nama_baru')";

            if (mysqli_query($koneksi, $query_insert)) {
                $pesan = "<div class='alert alert-success'>Booking berhasil diajukan! Menunggu verifikasi admin.</div>";
            } else {
                $pesan = "<div class='alert alert-danger'>Gagal: " . mysqli_error($koneksi) . "</div>";
            }
        }
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal mengunggah bukti transfer. Pastikan file valid!</div>";
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
    <style>
        /* Menggunakan style yang sama persis dengan login.php */
        .auth-container { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 120px 5% 60px 5%; }
        .auth-card { background: var(--bg-card, #fff); border: 1px solid var(--border-color, #eee); padding: 40px; border-radius: 24px; width: 100%; max-width: 450px; box-shadow: 0 10px 30px var(--shadow-color); }
        .auth-card h2 { font-family: 'Playfair Display', serif; margin-bottom: 25px; font-size: 2rem; color: var(--text-primary); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 0.9rem; font-weight: 600; color: var(--text-primary); }
        .form-control { width: 100%; padding: 14px 18px; border: 1px solid var(--border-color, #ddd); border-radius: 12px; background: var(--bg-secondary, transparent); color: var(--text-primary); font-family: inherit; font-size: 0.95rem; box-sizing: border-box; }
        .form-control:focus { outline: none; border-color: var(--accent-blue, #121a24); }
        
        .btn-submit { width: 100%; padding: 16px; margin-top: 10px; border: none; border-radius: 12px; background: var(--accent-blue, #121a24); color: #fff; font-weight: 700; font-size: 1rem; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: var(--accent-hover, #1f2937); }

        .alert { padding: 12px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; }
        .alert-success { background: #e6fffa; color: #065f46; border: 1px solid #b7f9e9; }
        .alert-danger { background: #ffebeb; color: #ad2a2a; border: 1px solid #fcc; }
    </style>
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <main class="auth-container">
        <div class="auth-card">
            <h2>Form Pemesanan</h2>
            
            <?php echo $pesan; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Pilih Paket Layanan</label>
                    <input type="hidden" name="id_fotografer" id="input_fotografer" value="">
                    
                    <select name="id_portofolio" class="form-control" required>
                        <option value="">-- Pilih Paket & Fotografer --</option>
                        
                        <?php 
                        // Ini adalah bagian di mana Anda menaruh kode tersebut
                        if ($query_paket && mysqli_num_rows($query_paket) > 0) {
                            mysqli_data_seek($query_paket, 0); 
                            while($pk = mysqli_fetch_assoc($query_paket)) : 
                        ?>
                                <option value="<?php echo htmlspecialchars($pk['id_portofolio'] ?? ''); ?>" 
                                        data-fotografer="<?php echo htmlspecialchars($pk['id_fotografer'] ?? ''); ?>">
                                    <?php 
                                        $nama_paket = $pk['package_name'] ?? 'Paket';
                                        $nama_foto = $pk['nama_fotografer'] ?? 'Fotografer Tidak Ditemukan';
                                        echo htmlspecialchars($nama_paket . " (" . $nama_foto . ")"); 
                                    ?>
                                </option>
                        <?php 
                            endwhile; 
                        } else {
                            echo "<option value=''>Tidak ada paket tersedia</option>";
                        }
                        ?>
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
                    <textarea name="catatan_tambahan" class="form-control" style="min-height: 100px; resize: vertical;" placeholder="Tulis catatan tambahan..."></textarea>
                </div>

                <div class="payment-info" style="background: #f8f9fa; padding: 20px; border-radius: 10px; border: 1px dashed #ccc; margin-bottom: 20px;">
                    <h5><i class="fa-solid fa-wallet"></i> Informasi Pembayaran</h5>
                    <p>Silakan transfer biaya layanan ke rekening berikut:</p>
                    <div style="font-weight: bold; font-size: 1.2rem; margin-bottom: 10px;">
                        BANK BCA: 1234567890 (A.N. Maison Etoira)
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="bukti_transfer">Upload Bukti Transfer</label>
                    <input type="file" name="bukti_transfer" class="form-control" accept="image/*" required>
                    <small class="text-muted">Format: JPG, PNG, atau JPEG.</small>
                </div>

                <button type="submit" name="konfirmasi_booking" class="btn-submit">Konfirmasi Booking</button>
            </form>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>
    <script>
    document.querySelector('select[name="id_portofolio"]').addEventListener('change', function() {
        var id_foto = this.options[this.selectedIndex].getAttribute('data-fotografer');
        docu<script>
    document.querySelector('select[name="id_portofolio"]').addEventListener('change', function() {
        // Ambil nilai dari atribut data-fotografer
        var id_foto = this.options[this.selectedIndex].getAttribute('data-fotografer');
    
    // Masukkan ke input hidden
    document.getElementById('input_fotografer').value = id_foto;
    
    // Debug: Cek apakah nilainya terambil (bisa dilihat di console browser F12 -> Console)
    console.log("ID Fotografer yang dipilih: " + id_foto);
});
</script>ment.getElementById('input_fotografer').value = id_foto;
    });
    </script>
</body>
</html>