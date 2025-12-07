<?php
// AplikasiWebKripto/public/dashboard/profil.php

// 1. Definisikan variabel spesifik untuk Halaman Profil ini
$currentPageTitle = "Profil Saya";
$pageSpecificCssFilename = "profil-styles.css"; 
$hideSearchInTopbar = true; 

// 2. Panggil file setup dan header HTML utama
// Ini akan menangani sesi, otentikasi, base URL ($mysqli juga), HTML head, dan body pembuka
require_once '../layouts/page_setup_header.php';

// BARU: Memuat fungsi helper untuk pencatatan log
// Path dari public/dashboard/ ke includes/
require_once __DIR__ . '/../../includes/log_helper.php'; 

// --- Logika untuk Ganti Password ---
$password_update_message = '';
$password_update_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_change_password'])) {
    if (!isset($_SESSION['user_id']) || !isset($mysqli)) { // Pastikan user_id dan $mysqli ada
        $password_update_error = "Sesi tidak valid atau koneksi database bermasalah. Silakan login kembali.";
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';
        $user_id_to_update = $_SESSION['user_id'];
        $username_for_log = $_SESSION['username'] ?? 'Tidak diketahui'; // Untuk deskripsi log

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            $password_update_error = "Semua field password harus diisi.";
            // Log opsional untuk field kosong jika dianggap penting
            // catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Upaya ganti password gagal (field kosong) oleh ' . $username_for_log . '.');
        } elseif (strlen($new_password) < 6) {
            $password_update_error = "Password baru minimal harus 6 karakter.";
            catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Upaya ganti password gagal (password baru terlalu pendek) oleh pengguna "' . $username_for_log . '".');
        } elseif ($new_password !== $confirm_new_password) {
            $password_update_error = "Password baru dan konfirmasi password tidak cocok.";
            catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Upaya ganti password gagal (konfirmasi password tidak cocok) oleh pengguna "' . $username_for_log . '".');
        } elseif ($new_password === $current_password) {
            $password_update_error = "Password baru tidak boleh sama dengan password saat ini.";
            catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Upaya ganti password gagal (password baru sama dengan lama) oleh pengguna "' . $username_for_log . '".');
        } else {
            $stmt_get_pass = $mysqli->prepare("SELECT password_hash FROM users WHERE id = ?");
            if ($stmt_get_pass) {
                $stmt_get_pass->bind_param("i", $user_id_to_update);
                $stmt_get_pass->execute();
                $result_pass = $stmt_get_pass->get_result();
                
                if ($user_data = $result_pass->fetch_assoc()) {
                    if (password_verify($current_password, $user_data['password_hash'])) {
                        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt_update_pass = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                        if ($stmt_update_pass) {
                            $stmt_update_pass->bind_param("si", $new_password_hashed, $user_id_to_update);
                            if ($stmt_update_pass->execute()) {
                                $password_update_message = "Password berhasil diperbarui!";
                                catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_SUCCESS', 'Pengguna "' . $username_for_log . '" berhasil mengubah password loginnya.');
                            } else {
                                $password_update_error = "Gagal memperbarui password di database: " . $stmt_update_pass->error;
                                catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Gagal eksekusi update password untuk pengguna "' . $username_for_log . '": ' . $stmt_update_pass->error);
                            }
                            $stmt_update_pass->close();
                        } else {
                             $password_update_error = "Gagal mempersiapkan statement update password: " . $mysqli->error;
                             catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Gagal prepare statement update password untuk pengguna "' . $username_for_log . '": ' . $mysqli->error);
                        }
                    } else {
                        $password_update_error = "Password saat ini yang Anda masukkan salah.";
                        catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Upaya ganti password gagal (password saat ini salah) oleh pengguna "' . $username_for_log . '".');
                    }
                } else {
                    $password_update_error = "Gagal mengambil data pengguna.";
                     catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Gagal mengambil data pengguna (ID: ' . $user_id_to_update . ') saat akan ganti password.');
                }
                $stmt_get_pass->close();
            } else {
                 $password_update_error = "Gagal mempersiapkan statement ambil password: " . $mysqli->error;
                 catat_log_aktivitas($mysqli, $user_id_to_update, 'PASSWORD_CHANGE_FAIL', 'Gagal prepare statement ambil password untuk pengguna "' . $username_for_log . '": ' . $mysqli->error);
            }
        }
    }
}
// --- Akhir Logika Ganti Password ---

// 3. Panggil file layout untuk Topbar
require_once '../layouts/header.php'; 
?>

<div class="dashboard-body-wrapper">
    <?php
        // 4. Panggil file layout untuk Sidebar
        require_once '../layouts/sidebar.php'; 
    ?>

    <main class="main-content" id="mainContent">
        <div class="content-header-placeholder">
             <h1><?php echo htmlspecialchars(explode(" - ", $currentPageTitle)[0]); ?></h1>
        </div>
        <div class="content-body">
            
            <div class="card">
                <h2>Informasi Akun</h2>
                <table class="profile-info-table">
                    <tr>
                        <th>Username</th>
                        <td>: <?php echo htmlspecialchars($username_loggedin); ?></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>: <?php echo htmlspecialchars(ucfirst($role_loggedin)); ?></td>
                    </tr>
                    <?php
                    if (isset($_SESSION['user_id']) && isset($mysqli)) {
                        $user_id_for_join_date = $_SESSION['user_id'];
                        $stmt_join_date = $mysqli->prepare("SELECT created_at FROM users WHERE id = ?");
                        if($stmt_join_date){
                            $stmt_join_date->bind_param("i", $user_id_for_join_date);
                            $stmt_join_date->execute();
                            $result_join_date = $stmt_join_date->get_result();
                            if($user_join_data = $result_join_date->fetch_assoc()){
                                echo "<tr><th>Bergabung Sejak</th><td>: " . date("d M Y, H:i", strtotime($user_join_data['created_at'])) . " WIB</td></tr>";
                            }
                            $stmt_join_date->close();
                        }
                    }
                    ?>
                </table>
            </div>

            <div class="card">
                <h2>Ubah Password Login Anda</h2>
                <?php if (!empty($password_update_message)): ?>
                    <div class="message success-message"><?php echo htmlspecialchars($password_update_message); ?></div>
                <?php endif; ?>
                <?php if (!empty($password_update_error)): ?>
                    <div class="message error-message"><?php echo htmlspecialchars($password_update_error); ?></div>
                <?php endif; ?>

                <form action="profil.php" method="POST" class="form-change-password">
                    <div class="form-group">
                        <label for="current_password">Password Saat Ini:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">Password Baru (minimal 6 karakter):</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="confirm_new_password">Konfirmasi Password Baru:</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" required minlength="6">
                    </div>
                    <button type="submit" name="submit_change_password" class="btn-submit-profile">Update Password</button>
                </form>
            </div>

        </div> </main> </div> <?php // Penutup .dashboard-body-wrapper ?>

<?php
// 6. Panggil file script dan penutup HTML utama kita
require_once '../layouts/page_scripts_footer.php';
?>