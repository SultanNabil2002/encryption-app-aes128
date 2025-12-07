<?php
// AplikasiWebKripto/public/layouts/page_setup_header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Memuat file konfigurasi database (untuk $mysqli)
// Path dari public/layouts/ ke config/
require_once __DIR__ . '/../../config/config.php'; 

// 2. BARU: Memuat file helper untuk fungsi pencatatan log
// Path dari public/layouts/ ke includes/
require_once __DIR__ . '/../../includes/log_helper.php'; 

// Pengecekan otentikasi yang ketat
if (!isset($isLoginPage) && !isset($isRegisterPage) && !isset($_SESSION['user_id'])) {
    // Jika variabel $isLoginPage dan $isRegisterPage tidak ada DAN user_id juga tidak ada di sesi
    $_SESSION['login_error'] = "Akses ditolak! Anda harus login terlebih dahulu.";
    header('Location: ../login.php'); // Path dari public/layouts/ ke public/login.php
    exit();
}

// Variabel ini akan digunakan di topbar, sidebar, dan konten halaman
// Ambil dari sesi jika sesi ada dan variabelnya terdefinisi
$username_loggedin = $_SESSION['username'] ?? 'User';
$role_loggedin = $_SESSION['role'] ?? 'Guest';

// --- Definisi Base URL ---
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

// Path untuk folder Assets (di dalam public)
$base_assets_url = $base_app_url . '/public/Assets'; 
// Path untuk navigasi dashboard
$dashboard_link_base = $base_app_url . '/public/dashboard'; 
$logout_link_path = $base_app_url . '/public/proses_logout.php';

// Menentukan halaman aktif untuk menu sidebar
$request_uri_path_for_active_menu = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 
$active_page_for_menu = basename($request_uri_path_for_active_menu);
$expected_dashboard_root_path_part = rtrim(parse_url($dashboard_link_base, PHP_URL_PATH),'/');
if ($request_uri_path_for_active_menu === $expected_dashboard_root_path_part || $request_uri_path_for_active_menu === $expected_dashboard_root_path_part . '/') {
    $active_page_for_menu = 'index.php';
}

// Logika Judul Halaman Dinamis dan CSS Spesifik Halaman
if (!isset($currentPageTitle)) {
    $currentPageTitle = "Dashboard Aplikasi Kripto"; // Judul default
}
if (!isset($pageSpecificCssFilename)) {
    $pageSpecificCssFilename = ""; 
}

$pageSpecificCssLink = "";
if (!empty($pageSpecificCssFilename)) {
    // CSS spesifik juga ada di public/Assets/css/
    $pageSpecificCssLink = '<link rel="stylesheet" href="' . htmlspecialchars($base_assets_url) . '/css/' . htmlspecialchars($pageSpecificCssFilename) . '">' . "\n";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($currentPageTitle); ?> - Aplikasi Kripto</title> 
    
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_assets_url); ?>/css/dashboard_styles.css"> 
    <?php if (!empty($pageSpecificCssLink)) { echo $pageSpecificCssLink; } ?>
    <link rel="icon" href="<?php echo htmlspecialchars($base_assets_url); ?>/img/logoPT_LazCoalMandiri.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="dashboard-page">
<?php
?>