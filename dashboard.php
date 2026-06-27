<?php
// 1. AKTIFKAN SESSION & PROTEKSI LOGIN
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_customer = $_SESSION['id_user'];
$pesan = "";

// 2. PROSES UPLOAD BUKTI PEMBAYARAN
if (isset($_POST['upload_bukti'])) {
    $id_booking = intval($_POST['id_booking']);
    $nama_file = $_FILES['bukti_transfer']['name'];
    $tmp_file = $_FILES['bukti_transfer']['tmp_name'];
    
    // Ambil ekstensi file
    $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
    // Bikin nama unik baru biar gambar tidak bentrok (contoh: bukti_36_172839.jpg)
    $nama_file_baru = "bukti_" . $id_customer . "_" . time() . "." . $ekstensi;
    
    // Folder tujuan upload sesuai dengan struktur folder master kita kemarin
    $target_dir = "uploads/bukti_transfer/" . $nama_file_baru;

    // Validasi format gambar
    $ekstensi_boleh = array('jpg', 'jpeg', 'png');
    if (in_array(strtolower($ekstensi), $ekstensi_boleh)) {
        if (move_uploaded_file($tmp_file, $target_dir)) {
            // Insert ke tabel payments
            $tgl_sekarang = date('Y-m-d');
            $query_pay = "INSERT INTO payments (id_booking, proof_image, payment_date, status) 
                          VALUES ($id_booking, '$nama_file_baru', '$tgl_sekarang', 'waiting')";
            
            if (mysqli_query($koneksi, $query_pay)) {
                // Update status di tabel bookings menjadi 'confirmed' atau tetap menunggu validasi admin
                mysqli_query($koneksi, "UPDATE bookings SET status = 'confirmed' WHERE id_booking = $id_booking");
                $pesan = "<div class='alert alert-success'>Bukti transfer berhasil diunggah! Menunggu verifikasi admin.</div>";
            }
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal mengunggah berkas fisik ke server!</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger'>Format file harus JPG, JPEG, atau PNG!</div>";
    }
}

// 3. QUERY UNTUK MENGAMBIL RIWAYAT BOOKING MILIK USER YANG SEDANG LOGIN
// PERBAIKAN: Ganti u.name menjadi u.nama, tapi ALIAS-nya tetap AS nama_fotografer
// Tambahkan port.price di bagian SELECT
$query_history = "SELECT b.*, u.nama AS nama_fotografer, pk.package_name, port.price 
                  FROM bookings b
                  JOIN photographers ph ON b.id_fotografer = ph.id_fotografer
                  JOIN users u ON ph.id_user = u.id_user
                  JOIN portofolio port ON b.id_portofolio = port.id_portofolio
                  JOIN packages pk ON port.id_paket = pk.id_package
                  WHERE b.id_customer = $id_customer
                  ORDER BY b.created_at DESC";

$result_history = mysqli_query($koneksi, $query_history) or die(mysqli_error($koneksi));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Saya - Maison Étoira</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght=0,600;1,400&display=swap" rel="stylesheet">
    <style>
        .dashboard-container { min-height: 80vh; padding: 120px 5% 60px 5%; }
        .dashboard-header { margin-bottom: 30px; }
        .dashboard-header h1 { font-family: 'Playfair Display', serif; font-size: 2.3rem; }
        .table-responsive { width: 100%; overflow-x: auto; background: var(--card-bg, #fff); border: 1px solid var(--border-color, #eee); border-radius: 12px; padding: 10px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.95rem; }
        th, td { padding: 16px; border-bottom: 1px solid var(--border-color, #eee); }
        th { font-weight: 600; color: var(--text-color); background-color: rgba(0,0,0,0.02); }
        .badge-status { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; display: inline-block; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-waiting_payment { background: #cce5ff; color: #004085; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-finished { background: #e2e3e5; color: #383d41; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-upload { background: var(--accent-color, #000); color: #fff; padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; text-decoration: none; font-weight: 500; }
        .alert { padding: 12px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 20px; }
        .alert-danger { background: #ffebeb; color: #ad2a2a; border: 1px solid #fcc; }
        .alert-success { background: #ebffeb; color: #2aad2a; border: 1px solid #cfc; }
    </style>
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1>Dashboard Pelanggan</h1>
            <p style="color: var(--text-muted);">Pantau status pengajuan jadwal sesi dokumentasi dan riwayat transaksi Anda di sini.</p>
        </div>

        <?php echo $pesan; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID Booking</th>
                        <th>Fotografer</th>
                        <th>Paket Layanan</th>
                        <th>Tanggal Foto</th>
                        <th>Total Biaya</th>
                        <th>Status Pesanan</th>
                        <th>Aksi Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result_history) > 0) {
                        while ($row = mysqli_fetch_assoc($result_history)) {
                    ?>
                            <tr>
                                <td>#M-<?php echo $row['id_booking']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['nama_fotografer']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['package_name']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
                                <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge-status status-<?php echo $row['status']; ?>">
                                        <?php 
                                        // Format teks status biar lebih rapi dibaca manusia
                                        echo str_replace('_', ' ', $row['status']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'waiting_payment') { ?>
                                        <form action="" method="POST" enctype="multipart/form-data" style="display: flex; gap: 5px; align-items: center;">
                                            <input type="hidden" name="id_booking" value="<?php echo $row['id_booking']; ?>">
                                            <input type="file" name="bukti_transfer" accept="image/*" required style="font-size: 0.8rem; width: 150px;">
                                            <button type="submit" name="upload_bukti" class="btn-upload">Kirim</button>
                                        </form>
                                    <?php } else if ($row['status'] == 'confirmed') { ?>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);">Lunas / Diproses</span>
                                    <?php } else { ?>
                                        <span style="font-size: 0.85rem; color: var(--text-muted);">-</span>
                                    <?php } ?>
                                </td>
                            </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; color:var(--text-muted); padding:30px;'>Anda belum pernah melakukan reservasi jasa fotografer.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include 'components/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>