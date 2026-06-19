<?php
session_start();
include 'koneksi.php';

// Ambil semua paket layanan dari database
$query = mysqli_query($koneksi, "SELECT * FROM packages");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Layanan - Maison Étoira</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            padding-top: 90px; /* Sesuaikan dengan tinggi navbar */
            background-color: #f8f9fa; /* Warna dasar sama kaya fotografer */
            color: #111; 
        }
     
        .hero-section { text-align: center; padding: 60px 20px 20px 20px; }
        .hero-section h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; margin-bottom: 12px; font-weight: 600; }
        .hero-section p { color: #555; max-width: 600px; margin: 0 auto; font-size: 1rem; line-height: 1.6; }

        .packages-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .package-card { 
            background-color: #fff;
            border: 1px solid #e5e7eb; 
            border-radius: 16px; 
            padding: 35px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            transition: transform 0.3s, box-shadow 0.3s; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
        }
        .package-card:hover { 
            transform: translateY(-6px); 
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08); 
        }
        
        .package-name { font-size: 1.4rem; font-weight: 700; margin: 0 0 10px 0; color: #111; }
        .package-price { font-size: 1.8rem; font-weight: 800; color: #111; margin-bottom: 20px; }
        .package-desc { color: #555; font-size: 0.95rem; line-height: 1.6; margin-bottom: 25px; flex-grow: 1; }
        
        .btn-booking { display: block; text-align: center; background: #111; color: #fff; padding: 14px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.2s; }
        .btn-booking:hover { background: #333; }
    </style>
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <section class="hero-section">
        <h1>Pilihan Paket Pemotretan</h1>
        <p>Temukan investasi memori terbaik yang dirancang khusus untuk menangkap keindahan setiap momen berhargamu.</p>
    </section>

    <main class="packages-grid">
        <?php while($row = mysqli_fetch_assoc($query)) : ?>
        <div class="package-card">
            <div>
                <h3 class="package-name"><?= htmlspecialchars($row['package_name']); ?></h3>
                <p class="package-desc">
                    <?= htmlspecialchars($row['description'] ?? 'Sesi foto premium eksklusif dengan pengaturan lighting studio profesional serta arahan gaya yang estetik.'); ?>
                </p>
            </div>
            <a href="booking.php?package_id=<?= $row['id_package']; ?>" class="btn-booking">Pilih Paket</a>
        </div>
        <?php endwhile; ?>
    </main>

</body>
</html>