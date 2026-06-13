# 📸 Maison Étoira Fotografi

## Sistem Informasi Pemesanan Jasa Fotografer Berbasis Web

Maison Étoira Fotografi merupakan sistem informasi berbasis web yang digunakan untuk membantu pelanggan dalam mencari, memilih, dan melakukan pemesanan jasa fotografer secara digital.

Sistem ini menghubungkan pelanggan dengan fotografer melalui fitur pencarian fotografer, portofolio, paket fotografi, pemesanan jasa, pembayaran, hingga pemberian ulasan.

---

# ✨ Fitur Sistem

## 👤 Pelanggan

- Registrasi akun
- Login pengguna
- Melihat daftar fotografer
- Melihat profil fotografer
- Melihat portofolio fotografer
- Melihat paket fotografi
- Melakukan booking jasa fotografi
- Melakukan pembayaran
- Melihat status pesanan
- Memberikan review dan rating


## 📷 Fotografer

- Login sebagai fotografer
- Mengelola profil fotografer
- Mengelola portofolio
- Mengelola paket fotografi
- Mengatur jadwal tersedia
- Melihat pesanan pelanggan


## 👨‍💼 Admin

- Mengelola pengguna
- Mengelola data fotografer
- Memverifikasi pembayaran
- Mengelola transaksi
- Melihat laporan sistem


---

# 🎨 Konsep Desain

Nama Website:

**Maison Étoira Fotografi**

Tema warna:

### Light Mode

- Putih
- Biru
- Navy


### Dark Mode

- Hitam
- Dark Blue
- Electric Blue


Desain dibuat dengan konsep:

- Modern
- Elegant
- Minimalist
- Professional Photography

---

# 🗄️ Database

Nama database:

```
maison_etoira_fotografi
```

Database menggunakan MySQL.

## Struktur Database

```
maison_etoira_fotografi

├── users
│
├── fotografer
│
├── portofolio
│
├── paket_fotografi
│
├── jadwal_fotografer
│
├── booking_jasa
│
├── pembayaran
│
├── status_pesanan
│
└── review_fotografer
```

---

# 🔗 Relasi Database

```
USERS

 |
 |
 └── FOTOGRAFER

          |
          ├── PORTOFOLIO
          |
          ├── PAKET_FOTOGRAFI
          |
          ├── JADWAL_FOTOGRAFER
          |
          └── BOOKING_JASA

                    |
                    ├── PEMBAYARAN
                    |
                    ├── STATUS_PESANAN
                    |
                    └── REVIEW_FOTOGRAFER
```

---

# 🛠️ Teknologi yang Digunakan

## Backend

- Laravel
- PHP
- MySQL


## Frontend

- React JS
- Vite
- Tailwind CSS


## Database

- MySQL
- phpMyAdmin


---

# 📂 Struktur Project

```
Maison-toira-Fotografi

│
├── backend
│   │
│   ├── database
│   │   └── maison_etoira_fotografi.sql
│   │
│   └── API
│
├── frontend
│
├── README.md
│
└── .gitignore
```

---

# 🚀 Instalasi Project

## Clone Repository

```bash
git clone https://github.com/nayla1119/Maison-toira-Fotografi.git
```

Masuk folder project:

```bash
cd Maison-toira-Fotografi
```

---

# Database Setup

1. Buka phpMyAdmin

```
http://localhost/phpmyadmin
```

2. Buat database:

```
maison_etoira_fotografi
```

3. Import file:

```
backend/database/maison_etoira_fotografi.sql
```

---

# 📱 Responsive Design

Website mendukung:

- Desktop
- Laptop
- Tablet
- Mobile


---

# 👩‍💻 Developer

**Nayla Putri**

Project:
**Maison Étoira Fotografi**

---

# 📌 Status Project

Saat ini:

✅ Database Design  
✅ Database Implementation  
⬜ Backend API  
⬜ Frontend Website  
⬜ Authentication System  
⬜ Deployment  


---

# License

Project ini dibuat untuk kebutuhan pembelajaran dan pengembangan sistem informasi.