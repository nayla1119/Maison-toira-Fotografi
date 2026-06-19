document.addEventListener("DOMContentLoaded", function () {
    
    // --- 1. HANDLING LOGIKA THEME LIGHT / DARK MODE ---
    const themeToggleBtn = document.getElementById("themeToggle");
    const bodyElement = document.body;

    // Cek apakah user sebelumnya sudah menyimpan pilihan tema di browser
    const savedTheme = localStorage.getItem("maison-theme");

    if (savedTheme === "dark") {
        bodyElement.classList.add("dark-mode");
    } else {
        bodyElement.classList.remove("dark-mode");
    }

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener("click", function () {
            bodyElement.classList.toggle("dark-mode");
            
            // Simpan perubahan ke localStorage agar tidak reset saat pindah halaman
            if (bodyElement.classList.contains("dark-mode")) {
                localStorage.setItem("maison-theme", "dark");
            } else {
                localStorage.setItem("maison-theme", "light");
            }
        });
    }

    // --- 2. HANDLING DROPDOWN MENU PROFIL ---
    const profileAvatarBtn = document.getElementById("profileAvatarBtn");
    const dropdownMenu = document.getElementById("dropdownMenu");

    if (profileAvatarBtn && dropdownMenu) {
        profileAvatarBtn.addEventListener("click", function (event) {
            event.stopPropagation(); // Mencegah event bubble up
            dropdownMenu.classList.toggle("show");
        });

        // Klik di luar menu untuk menutup dropdown profil otomatis
        document.addEventListener("click", function () {
            if (dropdownMenu.classList.contains("show")) {
                dropdownMenu.classList.remove("show");
            }
        });
    }

    // --- 3. HANDLING LOGOUT EVENT CLEANING ---
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function (e) {
            e.preventDefault();
            if (confirm("Apakah Anda yakin ingin keluar dari sistem Maison Étoira?")) {
                window.location.href = "logout.php";
            }
        });
    }
});
