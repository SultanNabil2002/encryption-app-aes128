<?php
// - $username_loggedin (untuk menampilkan nama pengguna)
// - $dashboard_link_base (untuk link kembali ke beranda dashboard atau halaman lain)
// - $logout_link_path (untuk link logout)
// - $hideSearchInTopbar (boolean, optional, defaultnya false atau tidak diset akan menampilkan search bar)
// - $_GET['q'] (opsional, untuk mengisi kembali value di search input jika ada)

// Default value untuk $hideSearchInTopbar jika tidak diset oleh halaman pemanggil
if (!isset($hideSearchInTopbar)) {
    $hideSearchInTopbar = false; // Secara default, search bar tampil
}

// Ambil nilai pencarian yang ada untuk ditampilkan kembali di input field
$current_search_query = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';

?>
<header class="topbar" id="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">☰</button>
    </div>

    <?php 
    // Logika untuk menampilkan atau menyembunyikan search bar berdasarkan variabel $hideSearchInTopbar
    if ($hideSearchInTopbar !== true): 
    ?>
        <div class="topbar-center">
            <form class="search-form" 
                  action="<?php echo htmlspecialchars($dashboard_link_base ?? ''); ?>/daftar_dokumen.php" 
                  method="GET">
                <span class="search-icon-wrapper">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18px" height="18px"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                </span>
                <input type="text" name="q" class="search-input" placeholder="Cari dokumen..." value="<?php echo $current_search_query; ?>">
                <button type="submit" class="search-button">Cari</button>
            </form>
        </div>
    <?php else: ?>
        <div class="topbar-center">
            </div>
    <?php endif; ?>

    <div class="topbar-right">
        <span class="user-info">
            <svg class="user-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20px" height="20px"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <span class="username-text"><?php echo htmlspecialchars($username_loggedin ?? 'User'); ?></span>
        </span>
        </div>
</header>