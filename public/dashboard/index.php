<?php
// AplikasiWebKripto/public/dashboard/index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// PENTING: Memuat file konfigurasi dan helper di awal
require_once __DIR__ . '/../../config/config.php';      // Untuk koneksi $mysqli
require_once __DIR__ . '/../../includes/log_helper.php'; // Untuk fungsi catat_log_aktivitas() jika diperlukan

// Pengecekan otentikasi
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = "Akses ditolak! Anda harus login terlebih dahulu.";
    header('Location: ../login.php'); 
    exit();
}

$username_loggedin = $_SESSION['username'] ?? 'User';
$role_loggedin = $_SESSION['role'] ?? 'Guest';

// --- Definisi Base URL untuk Navigasi & Aset ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME']; 
$app_root_url_path = '';
$public_keyword = '/public/';
$public_pos = strpos($script_name, $public_keyword);
if ($public_pos !== false) {
    $app_root_url_path = substr($script_name, 0, $public_pos);
} else {
    $path_parts = explode('/', trim($script_name, '/'));
    $projectNameGuess = 'AplikasiWebKripto'; 
    if (isset($path_parts[0]) && $path_parts[0] === $projectNameGuess) {
        $app_root_url_path = '/' . $path_parts[0];
    }
}
$app_root_url_path = rtrim($app_root_url_path, '/');
$base_app_url = $protocol . $host . $app_root_url_path;
$dashboard_link_base = $base_app_url . '/public/dashboard'; 
$logout_link_path = $base_app_url . '/public/proses_logout.php';

// Menentukan halaman aktif untuk menu sidebar
$request_uri_path_for_active_menu = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 
$active_page_for_menu = basename($request_uri_path_for_active_menu);
if ($active_page_for_menu === '' || $active_page_for_menu === 'dashboard' || $active_page_for_menu === basename(rtrim($dashboard_link_base, '/'))) {
    $active_page_for_menu = 'index.php';
}

// Variabel untuk judul halaman dan CSS spesifik (jika ada)
$currentPageTitle = "Beranda Dashboard";
// $pageSpecificCssFilename = ""; // Tidak ada CSS spesifik untuk beranda saat ini
// $pageSpecificCssLink = ""; // Tidak dibuat jika filename kosong

// --- Logika untuk Statistik Cepat & Aktivitas Terbaru (Super Admin) ---
$stats = [];
$recent_activities = [];

if ($role_loggedin === 'super_admin') {
    // Statistik Dokumen
    $result_total_docs = $mysqli->query("SELECT COUNT(*) as total FROM documents");
    $stats['total_documents'] = $result_total_docs ? $result_total_docs->fetch_assoc()['total'] : 0;
    
    $result_enc_docs = $mysqli->query("SELECT COUNT(*) as total FROM documents WHERE file_status = 'Terenkripsi'");
    $stats['encrypted_documents'] = $result_enc_docs ? $result_enc_docs->fetch_assoc()['total'] : 0;
    
    $result_dec_docs = $mysqli->query("SELECT COUNT(*) as total FROM documents WHERE file_status = 'Terdekripsi'");
    $stats['decrypted_documents'] = $result_dec_docs ? $result_dec_docs->fetch_assoc()['total'] : 0;

    // Statistik Pengguna
    $result_users = $mysqli->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $result_users ? $result_users->fetch_assoc()['total'] : 0;

    // Statistik Log (total)
    $result_logs = $mysqli->query("SELECT COUNT(*) as total FROM activity_logs");
    $stats['total_logs'] = $result_logs ? $result_logs->fetch_assoc()['total'] : 0;

    // Ambil 5 Aktivitas Terbaru
    $stmt_recent_logs = $mysqli->prepare("SELECT timestamp, username, action_type, description FROM activity_logs ORDER BY timestamp DESC LIMIT 5");
    if ($stmt_recent_logs) {
        $stmt_recent_logs->execute();
        $result_recent = $stmt_recent_logs->get_result();
        while ($row = $result_recent->fetch_assoc()) {
            $recent_activities[] = $row;
        }
        $stmt_recent_logs->close();
    }
} elseif ($role_loggedin === 'admin') {
    // Statistik untuk Admin (misalnya, hanya total dokumen atau yang relevan)
    $result_total_docs_admin = $mysqli->query("SELECT COUNT(*) as total FROM documents");
    $stats['total_documents_admin_view'] = $result_total_docs_admin ? $result_total_docs_admin->fetch_assoc()['total'] : 0;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($currentPageTitle); ?> - Aplikasi Kripto</title> 
    <link rel="stylesheet" href="../Assets/css/dashboard_styles.css"> 
    <link rel="icon" href="../Assets/img/logoPT_LazCoalMandiri.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <?php // Jika ada $pageSpecificCssLink, bisa dicetak di sini ?>
</head>
<body class="dashboard-page">

    <header class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">☰</button>
        </div>
        <div class="topbar-center">
            <?php if ($active_page_for_menu === 'daftar_dokumen.php' && (!isset($hideSearchInTopbar) || $hideSearchInTopbar !== true) ): ?>
            <form class="search-form" action="<?php echo htmlspecialchars($dashboard_link_base); ?>/daftar_dokumen.php" method="GET">
                <span class="search-icon-wrapper">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18px" height="18px"><path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                </span>
                <input type="text" name="q" class="search-input" placeholder="Cari dokumen..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                <button type="submit" class="search-button">Cari</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="topbar-right">
            <span class="user-info">
                <svg class="user-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20px" height="20px"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <span class="username-text"><?php echo htmlspecialchars($username_loggedin); ?></span>
            </span>
        </div>
    </header>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand-logo">
            <img src="../Assets/img/logoPT_LazCoalMandiri.png" alt="Logo PT LazCoal Mandiri">
            <span>PT LazCoal Mandiri</span>
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

    <main class="main-content" id="mainContent">
        <div class="content-header-placeholder">
             <h1><?php echo htmlspecialchars(explode(" - ", $currentPageTitle)[0]); ?></h1>
        </div>
        <div class="content-body">
            <div class="card">
                <h2>Selamat Datang di Dashboard!</h2>
                <p>
                    Halo, <strong><?php echo htmlspecialchars($username_loggedin); ?></strong>! 
                    Anda login sebagai: <strong><?php echo htmlspecialchars(ucfirst($role_loggedin)); ?></strong>.
                </p>
                <p>Ini adalah halaman utama dashboard Aplikasi Kriptografi PT LazCoal Mandiri. Silakan gunakan menu di samping untuk navigasi.</p>
            </div>

            <?php if ($role_loggedin === 'super_admin'): ?>
                <div class="card">
                    <h3>Statistik Cepat</h3>
                    <div class="stats-container">
                        <div class="stat-item">Total Dokumen: <span><?php echo $stats['total_documents'] ?? 0; ?></span></div>
                        <div class="stat-item">Terenkripsi: <span><?php echo $stats['encrypted_documents'] ?? 0; ?></span></div>
                        <div class="stat-item">Terdekripsi (Disimpan): <span><?php echo $stats['decrypted_documents'] ?? 0; ?></span></div>
                        <div class="stat-item">Total Pengguna: <span><?php echo $stats['total_users'] ?? 0; ?></span></div>
                        <div class="stat-item">Total Log Aktivitas: <span><?php echo $stats['total_logs'] ?? 0; ?></span></div>
                    </div>
                </div>

                <div class="card">
                    <h3>Pintasan Cepat</h3>
                    <div class="quick-links">
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/enkripsi.php" class="quick-link-btn">Enkripsi File Baru</a>
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/daftar_dokumen.php" class="quick-link-btn">Lihat Semua Dokumen</a>
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/manajemen_pengguna.php" class="quick-link-btn">Manajemen Pengguna</a>
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/log_aktivitas.php" class="quick-link-btn">Log Aktivitas</a>
                    </div>
                </div>

                <div class="card">
                    <h3>Aktivitas Terbaru (5 Teratas)</h3>
                    <?php if (!empty($recent_activities)): ?>
                        <ul class="recent-activities-list">
                            <?php foreach ($recent_activities as $activity): ?>
                                <li>
                                    <small><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($activity['timestamp']))); ?> WIB</small><br>
                                    <strong><?php echo htmlspecialchars($activity['username'] ?? 'Sistem'); ?></strong>: 
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $activity['action_type'])); ?> - 
                                    <em><?php echo htmlspecialchars(substr($activity['description'], 0, 100)) . (strlen($activity['description']) > 100 ? '...' : ''); ?></em>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Belum ada aktivitas terbaru yang tercatat.</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($role_loggedin === 'admin'): ?>
                <div class="card">
                    <h3>Statistik Dokumen</h3>
                    <div class="stats-container">
                         <div class="stat-item">Total Dokumen di Sistem: <span><?php echo $stats['total_documents_admin_view'] ?? 0; ?></span></div>
                    </div>
                </div>
                <div class="card">
                    <h3>Pintasan Cepat</h3>
                    <div class="quick-links">
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/daftar_dokumen.php" class="quick-link-btn">Lihat Daftar Dokumen</a>
                        <a href="<?php echo htmlspecialchars($dashboard_link_base); ?>/profil.php" class="quick-link-btn">Profil Saya</a>
                    </div>
                </div>
                <div class="card">
                    <h3>Informasi</h3>
                     <p>Selamat bekerja! Gunakan menu navigasi untuk mengakses fitur yang tersedia untuk Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebarToggle && sidebar) { 
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('active'); 
                });
            }

            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992 && sidebar && sidebarToggle && sidebar.classList.contains('active')) { 
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickOnToggler = sidebarToggle.contains(event.target);

                    if (!isClickInsideSidebar && !isClickOnToggler) {
                        sidebar.classList.remove('active');
                    }
                }
            });
        });
    </script>
</body>
</html>