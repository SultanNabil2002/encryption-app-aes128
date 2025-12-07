<?php
// AplikasiWebKripto/public/dashboard/proses_unduh.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/log_helper.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Akses ditolak. Login diperlukan.";
    exit();
}

$document_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$download_type = trim($_GET['type'] ?? '');
// Parameter baru untuk menentukan nama file unduhan untuk tipe 'decrypted'
// 'stored' = gunakan nama file yang disimpan di server (decrypted_filename)
// 'original' = gunakan nama file asli (original_filename)
$disposition_name_type = trim($_GET['dname'] ?? 'original'); // Default ke 'original'

if (!$document_id || !in_array($download_type, ['encrypted', 'decrypted'])) {
    header("HTTP/1.1 400 Bad Request");
    echo "Permintaan tidak valid.";
    exit();
}

$file_path_on_server = null;
$filename_for_download = null;
$original_filename_for_log = null; 
$db_filepath_column = '';

$stmt_doc = null;
$columns_to_select = "original_filename, encrypted_filename, encrypted_filepath, decrypted_filename, decrypted_filepath, mime_type";

if ($download_type === 'encrypted') {
    $stmt_doc = $mysqli->prepare("SELECT " . $columns_to_select . " FROM documents WHERE id = ?");
    $db_filepath_column = 'encrypted_filepath';
} elseif ($download_type === 'decrypted') {
    $stmt_doc = $mysqli->prepare("SELECT " . $columns_to_select . " FROM documents WHERE id = ? AND file_status = 'Terdekripsi' AND decrypted_filepath IS NOT NULL AND decrypted_filename IS NOT NULL");
    $db_filepath_column = 'decrypted_filepath';
}

if (!$stmt_doc) {
    header("HTTP/1.1 500 Internal Server Error");
    // echo "Gagal mempersiapkan query database: " . $mysqli->error; // Jangan tampilkan error DB ke user
    echo "Terjadi kesalahan pada server.";
    exit();
}

$stmt_doc->bind_param("i", $document_id);
$stmt_doc->execute();
$result_doc = $stmt_doc->get_result();
$doc_data = $result_doc->fetch_assoc();
$stmt_doc->close();

if (!$doc_data) {
    $log_desc_fail = 'Percobaan unduh file (' . $download_type . ') gagal: Dokumen ID ' . $document_id . ' tidak ditemukan/valid oleh pengguna "' . ($_SESSION['username'] ?? 'N/A') . '".';
    catat_log_aktivitas($mysqli, $_SESSION['user_id'], strtoupper('FILE_DOWNLOAD_' . $download_type . '_FAIL'), $log_desc_fail, $document_id);
    
    header("HTTP/1.1 404 Not Found");
    echo "Dokumen tidak ditemukan atau tidak tersedia untuk tipe unduhan yang diminta.";
    exit();
}

// Tentukan nama file untuk diunduh pengguna
if ($download_type === 'encrypted') {
    $filename_for_download = $doc_data['encrypted_filename']; 
} else { // type == 'decrypted'
    if ($disposition_name_type === 'stored') {
        $filename_for_download = $doc_data['decrypted_filename']; // Nama unik server
    } else { // Default atau 'original'
        $filename_for_download = $doc_data['original_filename']; // Nama asli file
    }
}
$file_path_on_server = __DIR__ . "/../" . $doc_data[$db_filepath_column]; 
$original_filename_for_log = $doc_data['original_filename'];


if (file_exists($file_path_on_server) && is_readable($file_path_on_server)) {
    $mime_type_to_serve = 'application/octet-stream'; 
    if ($download_type === 'decrypted' && !empty($doc_data['mime_type'])) {
        $mime_type_to_serve = $doc_data['mime_type'];
    }

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type_to_serve);
    header('Content-Disposition: attachment; filename="' . basename($filename_for_download) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path_on_server));
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    readfile($file_path_on_server);

    $log_action = ($download_type === 'encrypted') ? 'FILE_DOWNLOAD_ENCRYPTED' : 'FILE_DOWNLOAD_DECRYPTED';
    $log_desc_success = 'Pengguna "' . ($_SESSION['username'] ?? 'N/A') . '" berhasil mengunduh file (' . $download_type . '): "' . $original_filename_for_log . '" (ID Dok: ' . $document_id . '). Nama file unduhan: "' . $filename_for_download . '".';
    catat_log_aktivitas($mysqli, $_SESSION['user_id'], $log_action, $log_desc_success, $document_id);
    
    exit;

} else {
    $log_desc_fail_path = 'Percobaan unduh file (' . $download_type . ') gagal: File fisik tidak ditemukan di server untuk dokumen ID ' . $document_id . ' oleh pengguna "' . ($_SESSION['username'] ?? 'N/A') . '". Path diharapkan: ' . $file_path_on_server;
    catat_log_aktivitas($mysqli, $_SESSION['user_id'], strtoupper('FILE_DOWNLOAD_' . $download_type . '_FAIL'), $log_desc_fail_path, $document_id);

    header("HTTP/1.1 404 Not Found");
    echo "Maaf, file yang diminta tidak ditemukan di server.";
    exit();
}

if (isset($mysqli)) {
    $mysqli->close();
}
?>