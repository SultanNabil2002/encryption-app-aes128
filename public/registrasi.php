<?php
// AplikasiWebKripto/public/registrasi.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika pengguna sudah login, arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard/index.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';      // Koneksi database $mysqli
require_once __DIR__ . '/../includes/log_helper.php'; // Memuat fungsi catat_log_aktivitas

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
$base_public_folder_url = $base_app_url . '/public/'; // Untuk <base href>

$errors = [];
$success_message = '';
$input_username = '';
$input_role = 'admin'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $input_role = $_POST['role'] ?? 'admin';

    $allowed_roles = ['admin', 'super_admin'];
    if (!in_array($input_role, $allowed_roles)) {
        $errors[] = "Pilihan role tidak valid.";
        $input_role = 'admin'; 
    }
    $processed_role = $input_role;

    if (empty($input_username)) {
        $errors[] = "Username tidak boleh kosong.";
    } elseif (strlen($input_username) < 3 || strlen($input_username) > 50) {
        $errors[] = "Username harus antara 3 dan 50 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $input_username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore (_).";
    }

    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong.";
    } elseif (strlen($password) < 3) {
        $errors[] = "Password minimal harus 3 karakter.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    if (empty($errors)) {
        $stmt_check = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("s", $input_username);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Username sudah terdaftar. Silakan gunakan username lain.";
            }
            $stmt_check->close();
        } else {
            $errors[] = "Kesalahan database (cek username): " . $mysqli->error;
        }
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt_insert = $mysqli->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        if ($stmt_insert) {
            $stmt_insert->bind_param("sss", $input_username, $password_hash, $processed_role);
            if ($stmt_insert->execute()) {
                $new_user_id = $mysqli->insert_id; // Dapatkan ID pengguna yang baru dibuat
                $success_message = "Registrasi berhasil! Username: " . htmlspecialchars($input_username) . ". Role: " . htmlspecialchars($processed_role) . ". Anda sekarang bisa <a href='login.php'>login</a>.";
                
                // Catat log aktivitas: USER_REGISTER_PUBLIC
                // Pelaku_user_id diisi dengan ID user yang baru dibuat, karena aksi ini terkait langsung dengannya.
                // Target_user_id juga diisi dengan ID user yang baru dibuat.
                catat_log_aktivitas(
                    $mysqli,
                    $new_user_id, // Pelaku bisa dianggap user itu sendiri atau null jika dianggap sistem yang memfasilitasi
                    'USER_REGISTER_PUBLIC',
                    'Pengguna baru "' . $input_username . '" dengan role "' . $processed_role . '" berhasil mendaftar (via publik).',
                    null, // Tidak ada target_document_id
                    $new_user_id // Target_user_id adalah pengguna yang baru dibuat
                );
                $input_username = ''; // Kosongkan field setelah sukses
            } else {
                $errors[] = "Registrasi gagal. Silakan coba lagi. Error: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        } else {
            $errors[] = "Kesalahan database (insert user): " . $mysqli->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengguna - Aplikasi Web Kripto</title>
    <base href="<?php echo htmlspecialchars($base_public_folder_url); ?>">
    <link rel="stylesheet" href="Assets/css/registrasi.css">
    <link rel="icon" href="Assets/img/logoPT_LazCoalMandiri.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Untuk .warning-text jika belum ada di registrasi.css Anda */
        .warning-text {font-size: 0.8em; color: #d9534f; margin-left: 5px; display: inline; font-weight: normal;}
    </style>
</head>
<body>
    <div class="page-container">
        <div class="logo-container">
            <img src="Assets/img/logoPT_LazCoalMandiri.png" alt="Logo PT LazCoal Mandiri" draggable="false">
        </div>
        <div class="form-wrapper">
            <div class="form-container">
                <h2>Registrasi Pengguna Baru</h2>

                <?php if (!empty($errors)): ?>
                    <div class="message errors">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="message success">
                        <?php echo $success_message; // Mengandung HTML link ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($success_message)): ?>
                <form action="registrasi.php" method="post" novalidate>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($input_username); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label>Pilih Role:</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="role" value="admin" <?php echo ($input_role === 'admin') ? 'checked' : ''; ?>> Admin
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="role" value="super_admin" <?php echo ($input_role === 'super_admin') ? 'checked' : ''; ?>> Super Admin
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Registrasi</button>
                </form>
                <?php endif; ?>

                <div class="login-link">
                    Sudah punya akun? <a href="login.php">Login di sini</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>