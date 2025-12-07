<?php
// AplikasiWebKripto/public/dashboard/manajemen_pengguna.php

// 1. Variabel spesifik halaman dan pemanggilan header utama
$currentPageTitle = "Manajemen Pengguna";
$pageSpecificCssFilename = "manajemen_pengguna_styles.css"; 
$hideSearchInTopbar = true;

require_once '../layouts/page_setup_header.php'; // $mysqli, $role_loggedin, $dashboard_link_base, catat_log_aktivitas() tersedia

// 2. Otorisasi: Hanya Super Admin
if ($role_loggedin !== 'super_admin') {
    $_SESSION['dashboard_error_message'] = "Anda tidak memiliki izin untuk mengakses halaman Manajemen Pengguna.";
    header('Location: ' . ($dashboard_link_base ?? 'index.php') . '/index.php'); 
    exit();
}

// Inisialisasi pesan feedback
$add_user_message = '';
$add_user_error = '';
$delete_user_message = '';
$delete_user_error = '';

// --- Logika untuk Tambah Pengguna ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user_submit'])) {
    $new_username = trim($_POST['new_username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $new_role = $_POST['new_role'] ?? '';

    // Validasi input
    if (empty($new_username) || empty($new_password) || empty($confirm_password) || empty($new_role)) {
        $add_user_error = "Semua field harus diisi untuk menambah pengguna.";
    } elseif (strlen($new_username) < 3 || strlen($new_username) > 50) {
        $add_user_error = "Username baru harus antara 3 dan 50 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
        $add_user_error = "Username baru hanya boleh mengandung huruf, angka, dan underscore (_).";
    } elseif (strlen($new_password) < 3) { // DIPERBARUI: Minimal panjang password 3 karakter
        $add_user_error = "Password baru minimal harus 3 karakter.";
    } elseif ($new_password !== $confirm_password) {
        $add_user_error = "Password baru dan konfirmasi password tidak cocok.";
    } elseif (!in_array($new_role, ['admin', 'super_admin'])) {
        $add_user_error = "Role pengguna tidak valid.";
    } else {
        // Cek apakah username sudah ada
        $stmt_check_user = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        if ($stmt_check_user) {
            $stmt_check_user->bind_param("s", $new_username);
            $stmt_check_user->execute();
            $stmt_check_user->store_result();
            if ($stmt_check_user->num_rows > 0) {
                $add_user_error = "Username '" . htmlspecialchars($new_username) . "' sudah terdaftar.";
            }
            $stmt_check_user->close();
        } else {
            $add_user_error = "Gagal mempersiapkan pengecekan username: " . $mysqli->error;
        }

        if (empty($add_user_error)) { // Lanjutkan jika tidak ada error validasi
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Atau PASSWORD_ARGON2ID
            
            // Logika INSERT pengguna ke database
            $stmt_add = $mysqli->prepare("INSERT INTO users (username, password_hash, role, created_at) VALUES (?, ?, ?, NOW())");
            if ($stmt_add) {
                $stmt_add->bind_param("sss", $new_username, $hashed_password, $new_role);
                if ($stmt_add->execute()) {
                    $new_user_id_created = $mysqli->insert_id; // Dapatkan ID pengguna yang baru dibuat
                    $add_user_message = "Pengguna '" . htmlspecialchars($new_username) . "' dengan role '" . htmlspecialchars($new_role) . "' berhasil ditambahkan.";
                    
                    // Pencatatan log aktivitas
                    catat_log_aktivitas(
                        $mysqli, 
                        $_SESSION['user_id'], 
                        'USER_CREATE_ADMIN', 
                        'Super Admin "' . ($_SESSION['username'] ?? '') . '" membuat akun baru untuk "' . $new_username . '" (Role: ' . $new_role . ').',
                        null, // target_document_id
                        $new_user_id_created // target_user_id (user yang baru dibuat)
                    );
                } else {
                    $add_user_error = "Gagal menambahkan pengguna ke database: " . $stmt_add->error;
                }
                $stmt_add->close();
            } else {
                $add_user_error = "Gagal mempersiapkan statement tambah pengguna: " . $mysqli->error;
            }
        }
    }
}

// --- Logika untuk Hapus Pengguna ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user_submit'])) {
    $user_id_to_delete = filter_input(INPUT_POST, 'delete_user_id', FILTER_VALIDATE_INT);
    $username_to_delete_for_log = ""; 

    if ($user_id_to_delete) {
        if ($user_id_to_delete == $_SESSION['user_id']) {
            $delete_user_error = "Tidak dapat menghapus akun sendiri.";
        } else {
            $stmt_get_username = $mysqli->prepare("SELECT username, role FROM users WHERE id = ?");
            if($stmt_get_username){
                $stmt_get_username->bind_param("i", $user_id_to_delete);
                $stmt_get_username->execute();
                $result_user_to_delete = $stmt_get_username->get_result();
                if($user_to_delete_data = $result_user_to_delete->fetch_assoc()){
                    $username_to_delete_for_log = $user_to_delete_data['username'];
                    $role_to_delete = $user_to_delete_data['role'];

                    $is_last_super_admin = false;
                    if ($role_to_delete === 'super_admin') {
                        $stmt_count_sa = $mysqli->prepare("SELECT COUNT(*) as total_super_admin FROM users WHERE role = 'super_admin'");
                        if ($stmt_count_sa) {
                            $stmt_count_sa->execute();
                            $result_count_sa = $stmt_count_sa->get_result();
                            $count_data = $result_count_sa->fetch_assoc();
                            if ($count_data['total_super_admin'] <= 1) {
                                $is_last_super_admin = true;
                            }
                            $stmt_count_sa->close();
                        }
                    }

                    if ($is_last_super_admin) {
                        $delete_user_error = "Tidak dapat menghapus Super Admin terakhir.";
                    } else {
                        $stmt_delete = $mysqli->prepare("DELETE FROM users WHERE id = ?");
                        if ($stmt_delete) {
                            $stmt_delete->bind_param("i", $user_id_to_delete);
                            if ($stmt_delete->execute()) {
                                $delete_user_message = "Pengguna '" . htmlspecialchars($username_to_delete_for_log) . "' berhasil dihapus.";
                                catat_log_aktivitas(
                                    $mysqli, 
                                    $_SESSION['user_id'], 
                                    'USER_DELETE_ADMIN', 
                                    'Super Admin "' . ($_SESSION['username'] ?? '') . '" menghapus akun pengguna "' . $username_to_delete_for_log . '" (ID: ' . $user_id_to_delete . ').',
                                    null,
                                    $user_id_to_delete
                                );
                            } else {
                                $delete_user_error = "Gagal menghapus pengguna: " . $stmt_delete->error;
                            }
                            $stmt_delete->close();
                        } else {
                             $delete_user_error = "Gagal mempersiapkan statement hapus pengguna: " . $mysqli->error;
                        }
                    }
                } else {
                    $delete_user_error = "Pengguna yang akan dihapus tidak ditemukan.";
                }
                $stmt_get_username->close();
            } else {
                $delete_user_error = "Gagal mempersiapkan pengecekan pengguna: " . $mysqli->error;
            }
        }
    } else {
        $delete_user_error = "ID pengguna tidak valid untuk dihapus.";
    }
}

// --- Ambil Daftar Pengguna dari Database ---
$users_list = [];
$sql_get_users = "SELECT id, username, role, created_at FROM users ORDER BY created_at DESC";
$result_users = $mysqli->query($sql_get_users);
if ($result_users) {
    while ($row = $result_users->fetch_assoc()) {
        $users_list[] = $row;
    }
    $result_users->free();
}

// Panggil file layout untuk Topbar (milik Anda: header.php)
require_once '../layouts/header.php'; 
?>

<div class="dashboard-body-wrapper">
    <?php
        // Panggil file layout untuk Sidebar (milik Anda: sidebar.php)
        require_once '../layouts/sidebar.php'; 
    ?>

    <main class="main-content" id="mainContent">
        <div class="content-header-placeholder">
             <h1><?php echo htmlspecialchars(explode(" - ", $currentPageTitle)[0]); ?></h1>
        </div>
        <div class="content-body">
            
            <div class="card">
                <h2>Tambah Pengguna Baru</h2>
                <?php if (!empty($add_user_message)): ?>
                    <div class="message success-message"><?php echo htmlspecialchars($add_user_message); ?></div>
                <?php endif; ?>
                <?php if (!empty($add_user_error)): ?>
                    <div class="message error-message"><?php echo htmlspecialchars($add_user_error); ?></div>
                <?php endif; ?>
                <form action="manajemen_pengguna.php" method="POST" class="form-add-user">
                    <div class="form-row">
                        <div class="form-group column">
                            <label for="new_username">Username:</label>
                            <input type="text" id="new_username" name="new_username" required minlength="3" maxlength="50">
                        </div>
                        <div class="form-group column">
                            <label for="new_role">Role:</label>
                            <select id="new_role" name="new_role" required>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group column">
                            <label for="new_password">Password (min. 3 karakter):</label> 
                            <input type="password" id="new_password" name="new_password" required minlength="3"> 
                        </div>
                        <div class="form-group column">
                            <label for="confirm_password">Konfirmasi Password:</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="3"> 
                        </div>
                    </div>
                    <button type="submit" name="add_user_submit" class="btn-submit-user-mgm">Tambah Pengguna</button>
                </form>
            </div>

            <div class="card">
                <h2>Daftar Pengguna Sistem</h2>
                <?php if (!empty($delete_user_message)): ?>
                    <div class="message success-message"><?php echo htmlspecialchars($delete_user_message); ?></div>
                <?php endif; ?>
                <?php if (!empty($delete_user_error)): ?>
                    <div class="message error-message"><?php echo htmlspecialchars($delete_user_error); ?></div>
                <?php endif; ?>

                <div class="table-responsive-users">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Tanggal Bergabung</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users_list)): $counter = 1; ?>
                                <?php foreach ($users_list as $user_item): ?>
                                    <tr>
                                        <td data-label="No"><?php echo $counter++; ?></td>
                                        <td data-label="Username"><?php echo htmlspecialchars($user_item['username']); ?></td>
                                        <td data-label="Role"><?php echo htmlspecialchars(ucfirst($user_item['role'])); ?></td>
                                        <td data-label="Tanggal Bergabung"><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($user_item['created_at']))); ?></td>
                                        <td data-label="Aksi">
                                            <?php if ($user_item['id'] != $_SESSION['user_id']): ?>
                                                <form action="manajemen_pengguna.php" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna <?php echo htmlspecialchars(addslashes($user_item['username'])); ?>? Tindakan ini tidak dapat dibatalkan.');">
                                                    <input type="hidden" name="delete_user_id" value="<?php echo $user_item['id']; ?>">
                                                    <button type="submit" name="delete_user_submit" class="btn-delete-user">Hapus</button>
                                                </form>
                                            <?php else: ?>
                                                (Akun Ini)
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 20px;">Belum ada pengguna lain terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> 
    </main> 
</div> 

<?php
require_once '../layouts/page_scripts_footer.php';
?>