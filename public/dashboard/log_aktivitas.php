<?php

$currentPageTitle = "Log Aktivitas Sistem";
$pageSpecificCssFilename = "log_styles.css"; 
$hideSearchInTopbar = true; 

require_once '../layouts/page_setup_header.php'; // $mysqli, $role_loggedin, $dashboard_link_base tersedia

if ($role_loggedin !== 'super_admin') {
    $_SESSION['dashboard_error_message'] = "Anda tidak memiliki izin untuk mengakses halaman Log Aktivitas.";
    header('Location: ' . $dashboard_link_base . '/index.php'); 
    exit();
}

// catat_log_aktivitas($mysqli, $_SESSION['user_id'], 'VIEW_ACTIVITY_LOG', 'Pengguna "' . ($_SESSION['username'] ?? 'N/A') . '" melihat log aktivitas.');

// --- Pengaturan Filter dan Pagination ---
$logs_per_page = 15; // Jumlah log per halaman
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}
$offset = ($current_page - 1) * $logs_per_page;

// Ambil nilai filter dari URL (metode GET)
$filter_username = trim($_GET['search_user_log'] ?? '');
$filter_action_type = trim($_GET['action_type_filter_log'] ?? '');
$filter_date = trim($_GET['date_filter_log'] ?? '');

// Membangun query SQL dengan filter
$sql_base = "FROM activity_logs";
$where_clauses = [];
$params = []; // Untuk bind_param
$types = "";   // Untuk tipe data bind_param

if (!empty($filter_username)) {
    $where_clauses[] = "username LIKE ?";
    $params[] = "%" . $filter_username . "%";
    $types .= "s";
}
if (!empty($filter_action_type)) {
    $where_clauses[] = "action_type LIKE ?"; // Gunakan LIKE untuk pencarian pola
    $params[] = $filter_action_type . "%"; // Tambahkan '%' agar mencari semua yang diawali dengan nilai filter
    $types .= "s";
}
if (!empty($filter_date)) {
    $where_clauses[] = "DATE(timestamp) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Hitung total log untuk pagination (dengan filter yang sama)
$sql_total_logs = "SELECT COUNT(*) as total " . $sql_base . $where_sql;
$total_logs = 0;
$stmt_total = $mysqli->prepare($sql_total_logs);
if ($stmt_total) {
    if (!empty($types)) { // Hanya bind jika ada parameter
        $stmt_total->bind_param($types, ...$params);
    }
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_logs = $result_total->fetch_assoc()['total'] ?? 0;
    $stmt_total->close();
}
$total_pages = ceil($total_logs / $logs_per_page);
if ($current_page > $total_pages && $total_pages > 0) { // Koreksi jika halaman saat ini melebihi total halaman
    $current_page = $total_pages;
    $offset = ($current_page - 1) * $logs_per_page;
}


// Ambil data log untuk halaman saat ini (dengan filter dan pagination)
$logs = [];
$sql_get_logs = "SELECT timestamp, username, action_type, description, ip_address " . 
                $sql_base . $where_sql . 
                " ORDER BY timestamp DESC LIMIT ? OFFSET ?";

// Tambahkan parameter untuk LIMIT dan OFFSET ke $params dan $types
$params_for_main_query = $params; // Salin params filter
$params_for_main_query[] = $logs_per_page;
$params_for_main_query[] = $offset;
$types_for_main_query = $types . "ii"; // Tambahkan tipe integer untuk limit dan offset

$stmt_get_logs = $mysqli->prepare($sql_get_logs);
if ($stmt_get_logs) {
    // Gunakan call_user_func_array untuk bind_param dinamis jika ada parameter
    if (!empty($types_for_main_query)) {
         $stmt_get_logs->bind_param($types_for_main_query, ...$params_for_main_query);
    } else {

    }
   
    if ($stmt_get_logs->execute()) {
        $result_logs = $stmt_get_logs->get_result();
        while ($row = $result_logs->fetch_assoc()) {
            $logs[] = $row;
        }
    } else {
        error_log("Gagal eksekusi statement ambil log: " . $stmt_get_logs->error);
    }
    $stmt_get_logs->close();
} else {
    error_log("Gagal mempersiapkan statement ambil log: " . $mysqli->error);
}

require_once '../layouts/header.php'; // Topbar
?>

<div class="dashboard-body-wrapper">
    <?php require_once '../layouts/sidebar.php'; // Sidebar ?>

    <main class="main-content" id="mainContent">
        <div class="content-header-placeholder">
             <h1><?php echo htmlspecialchars(explode(" - ", $currentPageTitle)[0]); ?></h1>
        </div>
        <div class="content-body">
            <div class="card">
                <h2>Riwayat Aktivitas Sistem</h2>
                <p>Menampilkan log aktivitas dalam sistem. Gunakan filter di bawah untuk mencari log spesifik.</p>
                
                <form action="log_aktivitas.php" method="GET" class="log-filters">
                    <input type="text" placeholder="Cari berdasarkan username..." name="search_user_log" class="filter-input" value="<?php echo htmlspecialchars($filter_username); ?>">
                    <select name="action_type_filter_log" class="filter-select">
                        <option value="">Semua Tipe Aksi</option>
                        <?php 
                        $common_action_types = ['LOGIN_SUCCESS', 'LOGIN_FAIL', 'FILE_ENCRYPT', 'FILE_DECRYPT', 'USER_REGISTER_PUBLIC', 'PASSWORD_CHANGE_SUCCESS', 'PASSWORD_CHANGE_FAIL'];

                        foreach ($common_action_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo ($filter_action_type === $type) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(str_replace('_', ' ', $type)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" name="date_filter_log" class="filter-input" value="<?php echo htmlspecialchars($filter_date); ?>">
                    <button type="submit" class="btn-filter-log">Filter</button>
                    <a href="log_aktivitas.php" class="btn-filter-reset">Reset</a>
                </form>

                <div class="table-responsive-log">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Tipe Aksi</th>
                                <th>Deskripsi Aktivitas</th>
                                <th>Alamat IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs)): ?>
                                <?php foreach ($logs as $log_entry): ?>
                                    <tr>
                                        <td data-label="Waktu"><?php echo htmlspecialchars(date("d M Y, H:i:s", strtotime($log_entry['timestamp']))); ?></td>
                                        <td data-label="Pengguna"><?php echo htmlspecialchars($log_entry['username'] ?? 'N/A'); ?></td>
                                        <td data-label="Tipe Aksi">
                                            <span class="log-action-type <?php echo strtolower(htmlspecialchars($log_entry['action_type'])); ?>">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $log_entry['action_type'])); ?>
                                            </span>
                                        </td>
                                        <td data-label="Deskripsi"><?php echo nl2br(htmlspecialchars($log_entry['description'])); ?></td>
                                        <td data-label="Alamat IP"><?php echo htmlspecialchars($log_entry['ip_address'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 20px;">
                                        <?php 
                                        if (!empty($filter_username) || !empty($filter_action_type) || !empty($filter_date)) {
                                            echo "Tidak ada log yang cocok dengan filter Anda.";
                                        } else {
                                            echo "Belum ada aktivitas tercatat.";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&search_user_log=<?php echo urlencode($filter_username); ?>&action_type_filter_log=<?php echo urlencode($filter_action_type); ?>&date_filter_log=<?php echo urlencode($filter_date); ?>">&laquo; Sebelumnya</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&search_user_log=<?php echo urlencode($filter_username); ?>&action_type_filter_log=<?php echo urlencode($filter_action_type); ?>&date_filter_log=<?php echo urlencode($filter_date); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&search_user_log=<?php echo urlencode($filter_username); ?>&action_type_filter_log=<?php echo urlencode($filter_action_type); ?>&date_filter_log=<?php echo urlencode($filter_date); ?>">Berikutnya &raquo;</a>
                    <?php endif; ?>
                    <br>
                    <span class="page-info">Halaman <?php echo $current_page; ?> dari <?php echo $total_pages; ?> (Total <?php echo $total_logs; ?> log)</span>
                </div>
                <?php endif; ?>

            </div> 
        </div> 
    </main> 
</div> 

<?php
require_once '../layouts/page_scripts_footer.php';
?>