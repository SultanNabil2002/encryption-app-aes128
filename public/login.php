<?php
// AplikasiWebKripto/public/login.php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
}

// Jika pengguna sudah login, arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /AplikasiWebKripto/public/dashboard/index.php'); 
    exit;
}

require_once __DIR__ . '/../config/config.php';      // Koneksi database $mysqli
require_once __DIR__ . '/../includes/log_helper.php'; // BARU: Memuat fungsi catat_log_aktivitas dari file terpisah

// --- Definisi Base URL untuk HTML <base> ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name_for_base = $_SERVER['SCRIPT_NAME']; 
$app_root_url_path = '';
$public_keyword = '/public/';
$public_pos = strpos($script_name_for_base, $public_keyword);
if ($public_pos !== false) {
    $app_root_url_path = substr($script_name_for_base, 0, $public_pos);
} else {
    $path_parts_base = explode('/', trim($script_name_for_base, '/'));
    if (isset($path_parts_base[0]) && $path_parts_base[0] === 'AplikasiWebKripto') { 
        $app_root_url_path = '/' . $path_parts_base[0];
    }
}
$app_root_url_path = rtrim($app_root_url_path, '/');
$base_app_url = $protocol . $host . $app_root_url_path;
$base_public_folder_url = $base_app_url . '/public/';

// ----------------------------------------------------------------------
// TIDAK ADA LAGI DEFINISI FUNGSI catat_log_aktivitas() DI SINI
// karena sudah dipanggil dari log_helper.php
// ----------------------------------------------------------------------

$error_message = '';
$input_username = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($input_username) || empty($password)) {
        $error_message = "Username dan password tidak boleh kosong.";
    } else {
        $stmt = $mysqli->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $input_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password_hash'])) {
                    // Password cocok
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Catat log aktivitas: LOGIN_SUCCESS
                    catat_log_aktivitas(
                        $mysqli, 
                        $user['id'], 
                        'LOGIN_SUCCESS', 
                        'Pengguna "' . $user['username'] . '" berhasil login.'
                    );

                    header('Location: /AplikasiWebKripto/public/dashboard/index.php'); 
                    exit;
                } else {
                    // Password tidak cocok
                    $error_message = "Username atau password salah.";
                    catat_log_aktivitas(
                        $mysqli, 
                        $user['id'], 
                        'LOGIN_FAIL', 
                        'Percobaan login gagal untuk username: "' . $input_username . '" (password salah).'
                    );
                }
            } else {
                // Username tidak ditemukan
                $error_message = "Username atau password salah.";
                catat_log_aktivitas(
                    $mysqli, 
                    null, 
                    'LOGIN_FAIL', 
                    'Percobaan login gagal untuk username: "' . $input_username . '" (username tidak ditemukan).'
                );
            }
            $stmt->close();
        } else {
            $error_message = "Terjadi kesalahan pada server. Silakan coba lagi nanti.";
            error_log("Database prepare statement error in login.php: " . $mysqli->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Web Kripto</title>
    <base href="<?php echo htmlspecialchars($base_public_folder_url); ?>">
    <link rel="stylesheet" href="Assets/css/login.css"> 
    <link rel="icon" href="Assets/img/logoPT_LazCoalMandiri.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-container">
        <div class="logo-container">
            <img src="Assets/img/logoPT_LazCoalMandiri.png" alt="Logo PT LazCoal Mandiri" draggable="false">
        </div>
        <div class="form-wrapper">
            <div class="form-container">
                <h2>Login Aplikasi</h2>

                <?php if (!empty($error_message)): ?>
                    <div class="message errors">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post" novalidate>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($input_username); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn-submit">Login</button>
                </form>

                <div class="register-link">
                    Belum punya akun? <a href="registrasi.php">Daftar di sini</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>