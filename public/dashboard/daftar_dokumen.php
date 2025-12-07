<?php
// AplikasiWebKripto/public/dashboard/daftar_dokumen.php

$currentPageTitle = "Daftar Dokumen";
$pageSpecificCssFilename = "daftar_dokumen_styles.css"; 
$hideSearchInTopbar = false; // Search bar di topbar akan tampil

require_once '../layouts/page_setup_header.php'; 
$document_action_message = '';
$document_action_error = '';

if (isset($_SESSION['document_action_message'])) {
    $document_action_message = $_SESSION['document_action_message'];
    unset($_SESSION['document_action_message']);
}
if (isset($_SESSION['document_action_error'])) {
    $document_action_error = $_SESSION['document_action_error'];
    unset($_SESSION['document_action_error']);
}

// --- Pengaturan Filter Pencarian dan Pagination ---
$docs_per_page = 10; // Jumlah dokumen per halaman (Anda bisa ubah sesuai keinginan)
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;
$offset = ($current_page - 1) * $docs_per_page;

$search_query = trim($_GET['q'] ?? '');

// Membangun query SQL dengan filter pencarian
$sql_base = "FROM documents d JOIN users u ON d.user_id = u.id"; 
$where_clauses = [];
$params = []; 
$types = "";   

if (!empty($search_query)) {
    $where_clauses[] = "(d.original_filename LIKE ? OR 
                         d.encrypted_filename LIKE ? OR 
                         d.decrypted_filename LIKE ? OR 
                         d.description LIKE ? OR 
                         u.username LIKE ?)";
    $search_term_like = "%" . $search_query . "%";
    for ($i = 0; $i < 5; $i++) { // Ada 5 kondisi LIKE di atas
        $params[] = $search_term_like;
        $types .= "s";
    }
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Hitung total dokumen untuk pagination
$sql_total_docs = "SELECT COUNT(d.id) as total " . $sql_base . $where_sql;
$total_docs = 0;
$stmt_total = $mysqli->prepare($sql_total_docs);
if ($stmt_total) {
    if (!empty($types)) { 
        $stmt_total->bind_param($types, ...$params);
    }
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_docs = $result_total->fetch_assoc()['total'] ?? 0;
    $stmt_total->close();
}
$total_pages = ceil($total_docs / $docs_per_page);
if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
    $offset = ($current_page - 1) * $docs_per_page;
}

// Ambil data dokumen untuk halaman saat ini
$documents_list = [];
$sql_get_docs = "SELECT d.id, d.original_filename, d.encrypted_filename, d.decrypted_filename, 
                        d.filesize_original_kb, d.filesize_encrypted_kb, d.filesize_decrypted_kb,
                        d.encryption_duration_seconds, d.decryption_duration_seconds,
                        u.username AS uploader_username, d.description, d.file_status, 
                        d.upload_timestamp, d.encrypted_filepath, d.decrypted_filepath " . 
                $sql_base . $where_sql . 
                " ORDER BY d.upload_timestamp DESC LIMIT ? OFFSET ?";

$params_for_main_query = $params; 
$params_for_main_query[] = $docs_per_page;
$params_for_main_query[] = $offset;
$types_for_main_query = $types . "ii"; 

$stmt_get_docs = $mysqli->prepare($sql_get_docs);
if ($stmt_get_docs) {
    if (!empty($types_for_main_query)) {
         $stmt_get_docs->bind_param($types_for_main_query, ...$params_for_main_query);
    }
   
    if ($stmt_get_docs->execute()) {
        $result_docs = $stmt_get_docs->get_result();
        while ($row = $result_docs->fetch_assoc()) {
            $documents_list[] = $row;
        }
    } else {
        // error_log("Gagal eksekusi statement ambil dokumen: " . $stmt_get_docs->error);
    }
    $stmt_get_docs->close();
} else {
    // error_log("Gagal mempersiapkan statement ambil dokumen: " . $mysqli->error);
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
                <h2>Semua Dokumen Terkelola</h2>
                <?php if (!empty($search_query)): ?>
                    <p class="search-results-info">Hasil pencarian untuk: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>. Ditemukan <?php echo $total_docs; ?> dokumen.</p>
                <?php else: ?>
                    <p>Total <?php echo $total_docs; ?> dokumen dalam sistem.</p>
                <?php endif; ?>
                
                <div class="table-responsive-docs"> 
                    <table class="document-table"> 
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama File Asli</th>
                                <th>Nama File Terenkripsi</th>
                                <th>Nama File Didekripsi</th>
                                <th>Ukuran Asli (KB)</th>
                                <th>Ukuran Terenkripsi (KB)</th>
                                <th>Ukuran Didekripsi (KB)</th>
                                <th>Durasi Enkripsi (s)</th>
                                <th>Durasi Dekripsi (s)</th>
                                <th>Pengunggah</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($documents_list)): $counter = $offset + 1; ?>
                                <?php foreach ($documents_list as $doc): ?>
                                    <tr>
                                        <td data-label="No"><?php echo $counter++; ?></td>
                                        <td data-label="Nama File Asli"><?php echo htmlspecialchars($doc['original_filename']); ?></td>
                                        <td data-label="Nama File Terenkripsi"><?php echo htmlspecialchars($doc['encrypted_filename'] ?? '-'); ?></td>
                                        <td data-label="Nama File Didekripsi"><?php echo ($doc['file_status'] === 'Terdekripsi' && !empty($doc['decrypted_filename'])) ? htmlspecialchars($doc['decrypted_filename']) : '-'; ?></td>
                                        <td data-label="Ukuran Asli (KB)"><?php echo htmlspecialchars($doc['filesize_original_kb'] ?? '-'); ?></td>
                                        <td data-label="Ukuran Terenkripsi (KB)"><?php echo htmlspecialchars($doc['filesize_encrypted_kb'] ?? '-'); ?></td>
                                        <td data-label="Ukuran Didekripsi (KB)"><?php echo ($doc['file_status'] === 'Terdekripsi' && !empty($doc['filesize_decrypted_kb'])) ? htmlspecialchars($doc['filesize_decrypted_kb']) : '-'; ?></td>
                                        <td data-label="Durasi Enkripsi (s)"><?php echo htmlspecialchars($doc['encryption_duration_seconds'] ?? '-'); ?></td>
                                        <td data-label="Durasi Dekripsi (s)"><?php echo ($doc['file_status'] === 'Terdekripsi' && !empty($doc['decryption_duration_seconds'])) ? htmlspecialchars($doc['decryption_duration_seconds']) : '-'; ?></td>
                                        <td data-label="Pengunggah"><?php echo htmlspecialchars($doc['uploader_username'] ?? 'N/A'); ?></td>
                                        <td data-label="Deskripsi" class="description-cell">
                                            <?php 
                                            $desc_snippet = htmlspecialchars($doc['description'] ?? '-');
                                            if (strlen($desc_snippet) > 50) {
                                                $desc_snippet = substr($desc_snippet, 0, 50) . "...";
                                            }
                                            echo $desc_snippet;
                                            ?>
                                        </td>
                                        <td data-label="Status">
                                            <span class="doc-status <?php echo strtolower(htmlspecialchars($doc['file_status'])); ?>">
                                                <?php echo htmlspecialchars($doc['file_status']); ?>
                                            </span>
                                        </td>
                                        <td data-label="Aksi" class="actions-cell">
                                            <?php if ($doc['file_status'] === 'Terenkripsi'): ?>
                                                <?php // AKSI UNTUK FILE STATUS TERENKRIPSI ?>
                                                <a href="proses_unduh.php?type=encrypted&id=<?php echo $doc['id']; ?>" class="btn-action btn-download-enc" title="Unduh Versi Terenkripsi (.enc)">Unduh .enc</a>
                                                
                                                <?php if ($role_loggedin === 'super_admin'): ?>
                                                    <a href="dekripsi.php?id=<?php echo $doc['id']; ?>" class="btn-action btn-decrypt" title="Dekripsi File Ini">Dekripsi</a>
                                                <?php endif; ?>

                                            <?php elseif ($doc['file_status'] === 'Terdekripsi'): ?>
                                                <?php // AKSI UNTUK FILE STATUS TERDEKRIPSI ?>
                                                <a href="proses_unduh.php?type=decrypted&id=<?php echo $doc['id']; ?>&dname=stored" class="btn-action btn-download-dec-stored" title="Unduh file hasil dekripsi (nama server)">Unduh File Terdekripsi</a>
                                                <!-- <a href="proses_unduh.php?type=decrypted&id=<?php echo $doc['id']; ?>&dname=original" class="btn-action btn-download-dec-original" title="Unduh file hasil dekripsi (nama asli)">Unduh Asli</a> -->
                                            <?php endif; ?>

                                            <?php if ($role_loggedin === 'super_admin'): ?>
                                                <?php // AKSI HAPUS (selalu ada untuk Super Admin) ?>
                                                <form action="proses_hapus_dokumen.php" method="POST" style="display:inline-block; margin-top:5px;" onsubmit="return confirm('Yakin ingin menghapus dokumen \'<?php echo htmlspecialchars(addslashes($doc['original_filename'])); ?>\'? Ini akan menghapus semua versi file terkait.');">
                                                    <input type="hidden" name="document_id_to_delete" value="<?php echo $doc['id']; ?>">
                                                    <?php // TODO: Tambahkan CSRF token ?>
                                                    <button type="submit" name="delete_document_submit" class="btn-action btn-delete" title="Hapus Dokumen">Hapus</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="13" style="text-align:center; padding: 20px;">
                                        <?php echo !empty($search_query) ? "Tidak ada dokumen yang cocok dengan pencarian Anda." : "Belum ada dokumen dalam sistem."; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                        $query_params_pagination = [];
                        if (!empty($search_query)) $query_params_pagination['q'] = $search_query;
                        $query_string_pagination = http_build_query($query_params_pagination);
                    ?>
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?php echo $current_page - 1; ?>&<?php echo $query_string_pagination; ?>">&laquo; Sebelumnya</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo $query_string_pagination; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?php echo $current_page + 1; ?>&<?php echo $query_string_pagination; ?>">Berikutnya &raquo;</a>
                    <?php endif; ?>
                    <br>
                    <span class="page-info">Halaman <?php echo $current_page; ?> dari <?php echo $total_pages; ?> (Total <?php echo $total_docs; ?> dokumen)</span>
                </div>
                <?php endif; ?>

            </div> 
        </div> 
    </main> 
</div> 

<?php
require_once '../layouts/page_scripts_footer.php';
?>