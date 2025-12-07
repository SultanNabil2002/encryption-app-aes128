<?php
// AplikasiWebKripto/public/dashboard/proses_hapus_dokumen.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';      // Koneksi $mysqli
require_once __DIR__ . '/../../includes/log_helper.php'; // Fungsi catat_log_aktivitas()

// Inisialisasi pesan feedback untuk halaman daftar_dokumen.php
$_SESSION['document_action_message'] = '';
$_SESSION['document_action_error'] = '';

// --- Pengecekan Sesi dan Otorisasi ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $_SESSION['document_action_error'] = "Sesi tidak valid atau Anda belum login.";
    header('Location: daftar_dokumen.php');
    exit();
}

if ($_SESSION['role'] !== 'super_admin') {
    $_SESSION['document_action_error'] = "Anda tidak memiliki izin untuk menghapus dokumen.";
    header('Location: daftar_dokumen.php');
    exit();
}

// --- Validasi Metode Request dan Input ---
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['delete_document_submit'])) {
    $_SESSION['document_action_error'] = "Akses tidak valid ke halaman proses hapus.";
    header('Location: daftar_dokumen.php');
    exit();
}

$document_id_to_delete = filter_input(INPUT_POST, 'document_id_to_delete', FILTER_VALIDATE_INT);
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'] ?? 'Super Admin';

if (!$document_id_to_delete) {
    $_SESSION['document_action_error'] = "ID Dokumen yang akan dihapus tidak valid.";
    header('Location: daftar_dokumen.php');
    exit();
}

// --- Ambil Detail Dokumen dari Database untuk Mendapatkan Path File ---
$doc_data = null;
$stmt_get_paths = $mysqli->prepare(
    "SELECT original_filename, encrypted_filepath, decrypted_filepath, file_status 
     FROM documents 
     WHERE id = ?"
);

if (!$stmt_get_paths) {
    $_SESSION['document_action_error'] = "Gagal mempersiapkan statement DB (ambil path): " . $mysqli->error;
    header('Location: daftar_dokumen.php');
    exit();
}

$stmt_get_paths->bind_param("i", $document_id_to_delete);
$stmt_get_paths->execute();
$result_paths = $stmt_get_paths->get_result();
if ($result_paths->num_rows === 1) {
    $doc_data = $result_paths->fetch_assoc();
}
$stmt_get_paths->close();

if (!$doc_data) {
    $_SESSION['document_action_error'] = "Dokumen dengan ID " . htmlspecialchars($document_id_to_delete) . " tidak ditemukan.";
    catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DELETE_FAIL', 'Percobaan hapus dokumen gagal: Dokumen ID ' . $document_id_to_delete . ' tidak ditemukan oleh pengguna "' . $current_username . '".', $document_id_to_delete);
    header('Location: daftar_dokumen.php');
    exit();
}

$original_filename_for_log = $doc_data['original_filename'];
$encrypted_file_path_relative = $doc_data['encrypted_filepath'];
$decrypted_file_path_relative = $doc_data['decrypted_filepath']; // Bisa NULL
$file_status_for_log = $doc_data['file_status'];


// --- Hapus File Fisik ---
$encrypted_file_deleted_successfully = true; // Anggap sukses jika path tidak ada/kosong
$decrypted_file_deleted_successfully = true; // Anggap sukses jika path tidak ada/kosong

// Hapus file terenkripsi
if (!empty($encrypted_file_path_relative)) {
    $encrypted_file_absolute_path = __DIR__ . "/../" . $encrypted_file_path_relative; // Dari public/dashboard/ ke public/Hasil/...
    if (file_exists($encrypted_file_absolute_path)) {
        if (!unlink($encrypted_file_absolute_path)) {
            $encrypted_file_deleted_successfully = false;
            $_SESSION['document_action_error'] .= "Gagal menghapus file terenkripsi fisik: " . htmlspecialchars($encrypted_file_path_relative) . ". ";
        }
    }
}

// Hapus file hasil dekripsi jika ada
if ($file_status_for_log === 'Terdekripsi' && !empty($decrypted_file_path_relative)) {
    $decrypted_file_absolute_path = __DIR__ . "/../" . $decrypted_file_path_relative;
    if (file_exists($decrypted_file_absolute_path)) {
        if (!unlink($decrypted_file_absolute_path)) {
            $decrypted_file_deleted_successfully = false;
            $_SESSION['document_action_error'] .= "Gagal menghapus file hasil dekripsi fisik: " . htmlspecialchars($decrypted_file_path_relative) . ". ";
        }
    }
}

// Jika salah satu file fisik gagal dihapus, hentikan dan beri pesan error
// (Namun, kita bisa juga memilih untuk tetap menghapus record DB, tergantung kebijakan)
if (!$encrypted_file_deleted_successfully || !$decrypted_file_deleted_successfully) {
    catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DELETE_FAIL', 'Gagal menghapus file fisik untuk dokumen "' . $original_filename_for_log . '" (ID: ' . $document_id_to_delete . ') oleh pengguna "' . $current_username . '".', $document_id_to_delete);
    header('Location: daftar_dokumen.php');
    exit();
}


// --- Hapus Record dari Database ---
$stmt_delete_doc = $mysqli->prepare("DELETE FROM documents WHERE id = ?");
if ($stmt_delete_doc) {
    $stmt_delete_doc->bind_param("i", $document_id_to_delete);
    if ($stmt_delete_doc->execute()) {
        if ($stmt_delete_doc->affected_rows > 0) {
            $_SESSION['document_action_message'] = "Dokumen '" . htmlspecialchars($original_filename_for_log) . "' (ID: " . $document_id_to_delete . ") dan file terkait berhasil dihapus.";
            catat_log_aktivitas(
                $mysqli, 
                $current_user_id, 
                'FILE_DELETE_SUCCESS', 
                'Pengguna "' . $current_username . '" berhasil menghapus dokumen "' . $original_filename_for_log . '" (ID: ' . $document_id_to_delete . '). File fisik terkait juga dihapus.',
                $document_id_to_delete
            );
        } else {
            // Ini bisa terjadi jika dokumen sudah dihapus di proses lain sebelum ini
            $_SESSION['document_action_error'] = "Record dokumen dengan ID " . htmlspecialchars($document_id_to_delete) . " tidak ditemukan di database (mungkin sudah dihapus). File fisik (jika ada) telah diperiksa.";
             catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DELETE_FAIL', 'Record dokumen ID ' . $document_id_to_delete . ' tidak ditemukan di DB saat akan dihapus oleh pengguna "' . $current_username . '".', $document_id_to_delete);
        }
    } else {
        $_SESSION['document_action_error'] = "Gagal menghapus record dokumen dari database: " . $stmt_delete_doc->error;
        catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DELETE_FAIL', 'Gagal hapus record dokumen "' . $original_filename_for_log . '" (ID: ' . $document_id_to_delete . ') dari DB oleh pengguna "' . $current_username . '": ' . $stmt_delete_doc->error, $document_id_to_delete);
    }
    $stmt_delete_doc->close();
} else {
    $_SESSION['document_action_error'] = "Gagal mempersiapkan statement hapus dokumen dari DB: " . $mysqli->error;
}

header('Location: daftar_dokumen.php');
exit();

?>