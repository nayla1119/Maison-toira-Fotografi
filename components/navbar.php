<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">Maison <span>Étoira</span></a>
        
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="fotografer.php">Fotografer</a></li>
            <li><a href="paket.php">Paket</a></li>
            <li><a href="cara-kerja.php">Cara Kerja</a></li>
            <li><a href="tentang-kami.php">Tentang Kami</a></li>
        </ul>

        <div class="nav-actions">
            <button id="themeToggle" class="theme-btn" aria-label="Toggle Theme">
                <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.22" x2="5.64" y2="17.84"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg class="moon-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>

            <?php if (isset($_SESSION['id_user'])) { ?>
                <div class="profile-dropdown-container" id="profileDropdownContainer">
                    <div class="profile-avatar" id="profileAvatarBtn">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 2)); ?>
                    </div>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <div class="dropdown-header">
                            <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong><br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Akses: <?php echo htmlspecialchars($_SESSION['role']); ?></span>
                        </div>
                        <hr style="margin: 10px 0; border: 0; border-top: 1px solid var(--border-color);">
                        <a href="dashboard.php">Dashboard Saya</a>
                        <a href="#" class="logout-link" id="logoutBtn">Keluar / Logout</a>
                    </div>
                </div>
            <?php } else { ?>
                <div class="auth-buttons-group" id="authButtonsGroup">
                    <a href="login.php" class="btn-text-login" style="margin-right: 15px; color: var(--text-color); text-decoration: none; font-size: 0.95rem; font-weight: 500;">Masuk</a>
                    <a href="register.php" class="btn btn-primary">Daftar</a>
                </div>
            <?php } ?>
            
            <button class="hamburger" id="hamburgerMenu" aria-label="Open Menu" style="display: none;">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>