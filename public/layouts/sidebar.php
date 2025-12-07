<aside class="sidebar" id="sidebar">
        <div class="sidebar-brand-logo">
            <img src="../assets/img/logoPT_LazCoalMandiri.png" alt="Logo PT LazCoal Mandiri">
            <span>PT LazCoalMandiri</span>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/index.php" class="<?php echo ($active_page_for_menu == 'index.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">🏠</span><span class="nav-text">Beranda</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/daftar_dokumen.php" class="<?php echo ($active_page_for_menu == 'daftar_dokumen.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">📄</span><span class="nav-text">Daftar Dokumen</span>
                    </a>
                </li>

                <?php if ($role_loggedin === 'super_admin'): ?>
                    <li class="menu-separator"><span>Fitur Utama</span></li>
                    <li>
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/enkripsi.php" class="<?php echo ($active_page_for_menu == 'enkripsi.php') ? 'active' : ''; ?>">
                            <span class="nav-icon">🔒</span><span class="nav-text">Enkripsi File</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/dekripsi.php" class="<?php echo ($active_page_for_menu == 'dekripsi.php') ? 'active' : ''; ?>">
                            <span class="nav-icon">🔓</span><span class="nav-text">Dekripsi File</span>
                        </a>
                    </li>
                    <li class="menu-separator"><span>Administrasi</span></li>
                    <li>
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/manajemen_pengguna.php" class="<?php echo ($active_page_for_menu == 'manajemen_pengguna.php') ? 'active' : ''; ?>">
                            <span class="nav-icon">👥</span><span class="nav-text">Manajemen Pengguna</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/log_aktivitas.php" class="<?php echo ($active_page_for_menu == 'log_aktivitas.php') ? 'active' : ''; ?>">
                            <span class="nav-icon">📋</span><span class="nav-text">Log Aktivitas</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <li class="menu-separator"><span>Lainnya</span></li>
                <li>
                    <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/informasi.php" class="<?php echo ($active_page_for_menu == 'informasi.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">ℹ️</span><span class="nav-text">Informasi</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/profil.php" class="<?php echo ($active_page_for_menu == 'profil.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">⚙️</span><span class="nav-text">Profil Saya</span>
                    </a>
                </li>
                <li class="sidebar-logout">
                    <a href="<?php echo htmlspecialchars($logout_link_path); ?>">
                        <span class="nav-icon">⏻</span><span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            &copy; <?php echo date("Y"); ?> PT LazCoal Mandiri
        </div>
    </aside>