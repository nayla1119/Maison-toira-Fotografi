<?php
session_start();
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Maison Étoira</title>
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            padding-top: 50px; /* Sesuaikan dengan tinggi navbar */ 
            background-color: #f8f9fa; 
            color: #111; 
        }
        
        .about-wrapper { max-width: 1100px; margin: 60px auto; padding: 0 20px; display: flex; gap: 60px; align-items: center; flex-wrap: wrap; }
        
        .about-visual { 
            flex: 1; 
            min-width: 300px;
            background: #111; 
            color: #fff; 
            padding: 70px 40px; 
            border-radius: 20px; 
            text-align: center; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .about-visual h2 { font-family: 'Playfair Display', serif; font-size: 2.4rem; margin: 0; letter-spacing: 2px; font-weight: 600; }
        
        .about-info { flex: 1; min-width: 300px; }
        .about-info h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; margin: 0 0 20px 0; color: #111; font-weight: 600; }
        .about-info p { color: #555; line-height: 1.7; font-size: 1rem; margin-bottom: 20px; }
        
        .value-box { display: flex; gap: 15px; margin-top: 30px; background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e5e7eb; }
        .value-icon { font-size: 1.4rem; color: #111; margin-top: 2px; }
        .value-text strong { display: block; font-size: 1.05rem; margin-bottom: 4px; color: #111; }
        .value-text p { font-size: 0.9rem; color: #666; margin: 0; line-height: 1.5; }
    </style>
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <main class="about-wrapper">
        <div class="about-visual">
            <h2>Maison Étoira</h2>
            <p style="color:#888; font-size:0.85rem; margin-top:10px;">Est. 2026 — Premium Photography</p>
        </div>

        <div class="about-info">
            <h1>Menangkap Esensi Seni Lewat Lensa</h1>
            <p>Maison Étoira lahir dari visi untuk menyediakan ekosistem fotografi premium yang mempertemukan talenta fotografer profesional berbakat dengan klien yang menghargai cita rasa seni visual tinggi.</p>
            <p>Kami meyakini bahwa setiap dokumentasi bukan sekadar jepretan rana biasa, melainkan sebuah komposisi matang yang menangkap emosi, atmosfer, dan karakter otentik dari subjek foto.</p>

            <div class="value-box">
                <div class="value-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <div class="value-text">
                    <strong>Estetika Sinematik</strong>
                    <p>Kami fokus pada arahan gaya natural dengan hasil tone warna film yang timeless dan berkelas.</p>
                </div>
            </div>
        </div>
    </main>

</body>
</html>