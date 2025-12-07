<?php
// 1. Variabel spesifik halaman dan pemanggilan header utama
$currentPageTitle = "Dekripsi File";
$pageSpecificCssFilename = "dekripsi_styles.css"; 
$hideSearchInTopbar = true; 

require_once '../layouts/page_setup_header.php'; // $mysqli, $role_loggedin, $dashboard_link_base, catat_log_aktivitas() tersedia

// 2. Otorisasi: Hanya Super Admin
if ($role_loggedin !== 'super_admin') {
    $_SESSION['dashboard_error_message'] = "Anda tidak memiliki izin untuk mengakses halaman Dekripsi File.";
    header('Location: ' . ($dashboard_link_base ?? 'index.php') . '/index.php'); 
    exit();
}

// 3. Menampilkan pesan feedback dari proses dekripsi
$decrypt_success_message = '';
$decrypt_error_message = '';

if (isset($_SESSION['decrypt_success'])) {
    $decrypt_success_message = $_SESSION['decrypt_success'];
    unset($_SESSION['decrypt_success']); 
}
if (isset($_SESSION['decrypt_error'])) {
    $decrypt_error_message = $_SESSION['decrypt_error'];
    unset($_SESSION['decrypt_error']); 
}

// --- Pengaturan Pagination untuk Daftar Dokumen Terenkripsi ---
$docs_per_page_decrypt = 5; // Jumlah dokumen terenkripsi per halaman di form ini
$current_page_decrypt = isset($_GET['page_docs']) ? (int)$_GET['page_docs'] : 1;
if ($current_page_decrypt < 1) $current_page_decrypt = 1;
$offset_decrypt = ($current_page_decrypt - 1) * $docs_per_page_decrypt;

// Ambil data dokumen yang HANYA terenkripsi untuk ditampilkan dalam tabel pilihan
$encrypted_documents_list = [];
$sql_base_encrypted_docs = "FROM documents d JOIN users u ON d.user_id = u.id WHERE d.file_status = 'Terenkripsi'";

// Hitung total dokumen terenkripsi untuk pagination
$sql_total_encrypted_docs = "SELECT COUNT(d.id) as total " . $sql_base_encrypted_docs;
$total_encrypted_docs = 0;
$result_total_enc = $mysqli->query($sql_total_encrypted_docs);
if ($result_total_enc) {
    $total_encrypted_docs = $result_total_enc->fetch_assoc()['total'] ?? 0;
    $result_total_enc->free();
}
$total_pages_decrypt = ceil($total_encrypted_docs / $docs_per_page_decrypt);
if ($current_page_decrypt > $total_pages_decrypt && $total_pages_decrypt > 0) {
    $current_page_decrypt = $total_pages_decrypt;
    $offset_decrypt = ($current_page_decrypt - 1) * $docs_per_page_decrypt;
}

// Ambil daftar dokumen terenkripsi untuk halaman saat ini
$sql_get_encrypted_docs = "SELECT d.id, d.original_filename, d.encrypted_filename, d.upload_timestamp, d.filesize_original_kb, u.username as uploader_username " .
                           $sql_base_encrypted_docs .
                           " ORDER BY d.upload_timestamp DESC LIMIT ? OFFSET ?";

$stmt_get_enc_docs = $mysqli->prepare($sql_get_encrypted_docs);
if ($stmt_get_enc_docs) {
    $stmt_get_enc_docs->bind_param("ii", $docs_per_page_decrypt, $offset_decrypt);
    if ($stmt_get_enc_docs->execute()) {
        $result_enc_docs = $stmt_get_enc_docs->get_result();
        while ($row = $result_enc_docs->fetch_assoc()) {
            $encrypted_documents_list[] = $row;
        }
    } else {
        // error_log("Gagal eksekusi statement ambil dokumen terenkripsi: " . $stmt_get_enc_docs->error);
    }
    $stmt_get_enc_docs->close();
} else {
    // error_log("Gagal mempersiapkan statement ambil dokumen terenkripsi: " . $mysqli->error);
}


// Panggil file layout untuk Topbar
require_once '../layouts/header.php'; 
?>

<div class="dashboard-body-wrapper">
    <?php require_once '../layouts/sidebar.php';  ?>

    <main class="main-content" id="mainContent">
        <div class="content-header-placeholder">
             <h1><?php echo htmlspecialchars(explode(" - ", $currentPageTitle)[0]); ?></h1>
        </div>
        <div class="content-body">
            
            <div class="card">
                <h2>Formulir Dekripsi Dokumen (AES-128)</h2>
                <p>Pilih ID Dokumen dari tabel di bawah yang ingin Anda dekripsi, lalu masukkan password enkripsi (chiperkey) yang sesuai.</p>

                <?php if (!empty($decrypt_success_message)): ?>
                    <div class="message success-message">
                        <h4>Dekripsi Berhasil!</h4>
                        <?php 
                        if (is_array($decrypt_success_message)) {
                            echo "<p><strong>File Asli:</strong> " . htmlspecialchars($decrypt_success_message['original_filename'] ?? '-') . "</p>";
                            echo "<p><strong>File Hasil Dekripsi Disimpan Sebagai:</strong> " . htmlspecialchars($decrypt_success_message['decrypted_filename_stored'] ?? '-') . "</p>";
                            echo "<p><strong>Lokasi:</strong> <code>public/" . htmlspecialchars($decrypt_success_message['decrypted_filepath_relative'] ?? '-') . "</code></p>";
                            echo "<p><strong>Ukuran File Hasil Dekripsi:</strong> " . htmlspecialchars($decrypt_success_message['decrypted_size_kb'] ?? '-') . " KB</p>";
                            echo "<p><strong>Durasi Dekripsi:</strong> " . htmlspecialchars($decrypt_success_message['duration'] ?? '-') . " detik</p>";
                            // Tambahkan link download jika dekripsi_proses.php menyediakannya
                            if (!empty($decrypt_success_message['download_link'])) {
                                echo "<p style='margin-top:10px;'><a href='" . htmlspecialchars($decrypt_success_message['download_link']) . "' class='btn-action btn-download-dec' download>Unduh File Hasil Dekripsi</a></p>";
                            }
                        } else {
                            echo nl2br(htmlspecialchars($decrypt_success_message)); 
                        }
                        ?>
                         <p style="margin-top:10px;"><a href="daftar_dokumen.php" class="btn-link-to-list">Lihat Daftar Lengkap Dokumen</a></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($decrypt_error_message)): ?>
                    <div class="message error-message">
                        <h4>Dekripsi Gagal!</h4>
                        <p><?php echo nl2br(htmlspecialchars($decrypt_error_message)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (empty($decrypt_success_message)): ?>
                <form action="dekripsi_proses.php" method="POST" enctype="multipart/form-data" class="form-decrypt">
                    <div class="form-group">
                        <label for="document_id_to_decrypt">ID Dokumen Terenkripsi:</label>
                        <input type="number" id="document_id_to_decrypt" name="document_id_to_decrypt" placeholder="Masukkan ID dari tabel di bawah" required style="width: 250px;">
                        <small class="form-text">Lihat ID pada tabel "Daftar Dokumen Terenkripsi untuk Didekripsi" di bawah.</small>
                    </div>

                    <div class="form-group">
                        <label for="decryption_password">Password Dekripsi (Chiperkey):</label>
                        <input type="password" id="decryption_password" name="decryption_password" required minlength="3">
                    </div>
                    
                    <button type="submit" name="decrypt_submit_button" class="btn-submit-decrypt">Dekripsi File Sekarang</button>
                </form>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2>Daftar Dokumen Terenkripsi untuk Didekripsi</h2>
                <p>Hanya menampilkan file dengan status "Terenkripsi".</p>
                <div class="table-responsive-docs">
                    <table class="document-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama File Asli</th>
                                <th>Nama File Terenkripsi</th>
                                <th>Tgl. Unggah</th>
                                <th>Pengunggah</th>
                                <th>Pilih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($encrypted_documents_list)): ?>
                                <?php foreach ($encrypted_documents_list as $doc): ?>
                                    <tr>
                                        <td data-label="ID"><?php echo $doc['id']; ?></td>
                                        <td data-label="Nama File Asli"><?php echo htmlspecialchars($doc['original_filename']); ?></td>
                                        <td data-label="Nama File Terenkripsi"><?php echo htmlspecialchars($doc['encrypted_filename']); ?></td>
                                        <td data-label="Tgl. Unggah"><?php echo htmlspecialchars(date("d M Y, H:i", strtotime($doc['upload_timestamp']))); ?></td>
                                        <td data-label="Pengunggah"><?php echo htmlspecialchars($doc['uploader_username'] ?? 'N/A'); ?></td>
                                        <td data-label="Pilih">
                                            <button type="button" class="btn-action btn-select-doc" onclick="selectDocumentForDecryption(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars(addslashes($doc['original_filename'])); ?>')">Dekripsi</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 20px;">Tidak ada dokumen yang saat ini berstatus "Terenkripsi".</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages_decrypt > 1): ?>
                <div class="pagination">
                    <?php if ($current_page_decrypt > 1): ?>
                        <a href="?page_docs=<?php echo $current_page_decrypt - 1; ?>">&laquo; Sebelumnya</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages_decrypt; $i++): ?>
                        <?php if ($i == $current_page_decrypt): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page_docs=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($current_page_decrypt < $total_pages_decrypt): ?>
                        <a href="?page_docs=<?php echo $current_page_decrypt + 1; ?>">Berikutnya &raquo;</a>
                    <?php endif; ?>
                    <br>
                    <span class="page-info">Halaman <?php echo $current_page_decrypt; ?> dari <?php echo $total_pages_decrypt; ?> (Total <?php echo $total_encrypted_docs; ?> dokumen terenkripsi)</span>
                </div>
                <?php endif; ?>
            </div>

        </div> 
    </main> 
</div> 

<?php
// Panggil file script dan penutup HTML utama
require_once '../layouts/page_scripts_footer.php';
?>

<script>
// JavaScript sederhana untuk mengisi field ID Dokumen saat tombol "Pilih Ini" diklik
function selectDocumentForDecryption(docId, docName) {
    const docIdInput = document.getElementById('document_id_to_decrypt');
    if (docIdInput) {
        docIdInput.value = docId;
        const passwordInput = document.getElementById('decryption_password');
        if(passwordInput) {
            passwordInput.focus();
        }
        alert('ID Dokumen ' + docId + ' ("' + docName + '") telah dipilih. Silakan masukkan password dekripsi.');
    }
}
</script>