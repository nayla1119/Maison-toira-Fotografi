<?php

session_start();
include 'koneksi.php';
// 1. Ambil keyword pencarian, kategori, dan lokasi dari URL
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($koneksi, $_GET['kategori']) : '';
$lokasi = isset($_GET['lokasi']) ? mysqli_real_escape_string($koneksi, $_GET['lokasi']) : '';
// 2. Racik Query SQL Dinamis
$query = "SELECT p.*, u.nama AS name
          FROM photographers p
          JOIN users u ON p.id_user = u.id_user
          WHERE u.role = 'fotografer'";
if ($search != '') {
    $query .= " AND u.nama LIKE '%$search%'";
}
// Filter kategori berdasarkan kata kunci di deskripsi keahlian
if ($kategori != '') {
    $query .= " AND p.description LIKE '%$kategori%'";
}
if ($lokasi != '') {
    $query .= " AND p.location = '$lokasi'";
}
$query .= " ORDER BY p.rating DESC";
$result = mysqli_query($koneksi, $query);
// Ambil daftar lokasi unik untuk filter lokasi
$query_lokasi = mysqli_query($koneksi, "SELECT DISTINCT location FROM photographers WHERE location IS NOT NULL AND location != ''");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitra Fotografer - Maison Étoira</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* --- UTILITY & RESET UNTUK HALAMAN INI --- */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            margin: 0;
            padding-top: 90px; /* Biar gak ketutupan navbar melayang */
        }
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        /* --- SEARCH & FILTER BAR (Sesuai Sketsa) --- */
        .filter-wrapper {
            display: flex;
            gap: 15px;
            margin-bottom: 35px;
            flex-wrap: wrap;
        }
        .search-box {
            flex: 2;
            min-width: 280px;
            position: relative;
        }
        .search-box input {
            width: 100%;
            padding: 14px 18px 14px 45px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background-color: var(--bg-card);
            color: var(--text-primary);
            font-size: 0.95rem;
            outline: none;
            transition: 0.3s;
        }
        .search-box::before {
            content: "🔍";
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem;
            opacity: 0.5;
        }
        .filter-select {
            flex: 1;
            min-width: 180px;
            padding: 14px 18px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            background-color: var(--bg-card);
            color: var(--text-primary);
            font-size: 0.95rem;
            outline: none;
            cursor: pointer;
        }
        .search-box input:focus, .filter-select:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px var(--shadow-color);
        }
        /* --- GRID KARTU FOTOGRAFER (3 Kolom Sesuai Sketsa) --- */
        .photographer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }
        .card-photographer {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px var(--shadow-color);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }
        .card-photographer:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }
        .card-img-frame {
            width: 100%;
            height: 260px;
            background-color: #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        /* Tampilan silang kotak jika gambar profile kosong (mirip simbol wireframe) */
        .card-img-frame .placeholder-wire {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(45deg, var(--bg-secondary) 25%, transparent 25%),
                        linear-gradient(-45deg, var(--bg-secondary) 25%, transparent 25%);
            background-size: 20px 20px;
            color: var(--text-secondary);
            font-size: 0.9rem;
            opacity: 0.6;
        }
        .card-img-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-body {
            padding: 22px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .card-body h3 {
            font-size: 1.25rem;
            color: var(--text-primary);
            font-weight: 700;
        }
        .info-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .star-icon { color: #f59e0b; font-size: 1.1rem; }
        .info-price {
            margin-top: 8px;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }
        .info-price span {
            display: block;
            font-size: 1.15rem;
            color: var(--accent-blue);
            font-weight: 700;
            margin-top: 2px;
        }
        .btn-detail {
            margin-top: 15px;
            padding: 12px;
            text-align: center;
            background-color: var(--accent-blue);
            color: #ffffff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-detail:hover {
            background-color: var(--accent-hover);
        }
        .no-data {
            text-align: center;
            padding: 50px;
            color: var(--text-secondary);
            grid-column: 1 / -1;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <?php include 'components/navbar.php'; ?>
    <div class="main-container">
        <form action="" method="GET">
            <div class="filter-wrapper">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Cari fotografer..." value="<?php echo htmlspecialchars($search); ?>" onchange="this.form.submit()">
                </div>
                <select name="kategori" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <option value="Wedding" <?php echo ($kategori == 'Wedding') ? 'selected' : ''; ?>>Wedding</option>
                    <option value="Pre-wedding" <?php echo ($kategori == 'Pre-wedding') ? 'selected' : ''; ?>>Pre-wedding</option>
                    <option value="Studio" <?php echo ($kategori == 'Studio') ? 'selected' : ''; ?>>Studio / Wisuda</option>
                    <option value="Event" <?php echo ($kategori == 'Event') ? 'selected' : ''; ?>>Event / Pesta</option>
                </select>
                <select name="lokasi" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Lokasi</option>
                    <?php while($row_loc = mysqli_fetch_assoc($query_lokasi)): ?>
                        <option value="<?php echo $row_loc['location']; ?>" <?php echo ($lokasi == $row_loc['location']) ? 'selected' : ''; ?>>
                            <?php echo $row_loc['location']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
        <div class="photographer-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($ft = mysqli_fetch_assoc($result)): ?>
                    <div class="card-photographer">
                        <div class="card-img-frame">
                            <?php 
                            // Pastikan nama file tidak kosong
                            $img_name = $ft['profile_image'];
                            $img_path = "assets/img/photographers/" . $img_name;
                            ?>
                            <?php if (!empty($img_name) && file_exists($img_path)): ?>
                                <img src="<?php echo $img_path; ?>" alt="Foto <?php echo htmlspecialchars($ft['name']); ?>">
                            <?php else: ?>
                            <div class="placeholder-wire">
                                <?php echo !empty($img_name) ? "File tidak ditemukan di: " . $img_path : "Tidak ada foto"; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($ft['name']); ?></h3>
                            <div class="info-rating">
                                <span class="star-icon">★</span>
                                <strong><?php echo $ft['rating']; ?></strong>
                                <span>(<?php echo htmlspecialchars($ft['experience']); ?>)</span>
                            </div>
                            <div class="info-price">
                                Mulai Dari
                                <span>Rp 1.000.000</span>
                            </div>
                            <a href="detail-fotografer.php?id=<?= isset($ft['id_fotografer']) ? htmlspecialchars($ft['id_fotografer']) : '#'; ?>" class="btn-detail">Lihat Portofolio</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">❌ Tidak ada fotografer yang cocok dengan pencarianmu.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>