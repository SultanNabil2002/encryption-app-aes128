<?php
// AplikasiWebKripto/public/dashboard/dekripsi_proses.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

set_time_limit(0);

// Memuat file konfigurasi, kelas AES, dan helper log
require_once __DIR__ . '/../../config/config.php';      // Untuk koneksi $mysqli
require_once __DIR__ . '/../../includes/aes128.php';    // Kelas AES128_CBC
require_once __DIR__ . '/../../includes/log_helper.php'; // Fungsi catat_log_aktivitas()

// --- Pengecekan Akses dan Metode Request ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $_SESSION['decrypt_error'] = "Sesi tidak valid atau Anda belum login.";
    header('Location: dekripsi.php');
    exit();
}

if ($_SESSION['role'] !== 'super_admin') {
    $_SESSION['decrypt_error'] = "Anda tidak memiliki izin untuk melakukan dekripsi.";
    header('Location: dekripsi.php'); // Atau ke beranda dashboard
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['decrypt_submit_button'])) {
    $_SESSION['decrypt_error'] = "Akses tidak valid ke halaman proses dekripsi.";
    header('Location: dekripsi.php');
    exit();
}

// --- Ambil dan Validasi Input ---
$document_id_to_decrypt = filter_input(INPUT_POST, 'document_id_to_decrypt', FILTER_VALIDATE_INT);
$decryption_password_input = $_POST['decryption_password'] ?? '';
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'] ?? 'Super Admin';

if (empty($document_id_to_decrypt) || empty($decryption_password_input)) {
    $_SESSION['decrypt_error'] = "ID Dokumen dan Password Dekripsi (chiperkey) harus diisi.";
    header('Location: dekripsi.php');
    exit();
}

// --- Ambil Detail Dokumen dari Database ---
$doc_data = null;
$stmt_get_doc = $mysqli->prepare(
    "SELECT id, original_filename, encrypted_filename, encrypted_filepath, 
            iv, kdf_salt, encryption_password_hash, encryption_password_salt 
     FROM documents 
     WHERE id = ? AND file_status = 'Terenkripsi'" // Hanya bisa dekripsi file yang statusnya 'Terenkripsi'
);

if (!$stmt_get_doc) {
    $_SESSION['decrypt_error'] = "Gagal mempersiapkan statement DB (ambil doc): " . $mysqli->error;
    header('Location: dekripsi.php');
    exit();
}

$stmt_get_doc->bind_param("i", $document_id_to_decrypt);
$stmt_get_doc->execute();
$result_doc = $stmt_get_doc->get_result();
if ($result_doc->num_rows === 1) {
    $doc_data = $result_doc->fetch_assoc();
}
$stmt_get_doc->close();

if (!$doc_data) {
    $_SESSION['decrypt_error'] = "Dokumen terenkripsi dengan ID " . htmlspecialchars($document_id_to_decrypt) . " tidak ditemukan atau statusnya bukan 'Terenkripsi'.";
    catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DECRYPT_FAIL', 'Percobaan dekripsi gagal: Dokumen ID ' . $document_id_to_decrypt . ' tidak ditemukan/status salah oleh pengguna "' . $current_username . '".', $document_id_to_decrypt);
    header('Location: dekripsi.php');
    exit();
}

// --- Verifikasi Password Enkripsi Asli ---
// Password yang diinput di-hash menggunakan salt yang tersimpan dari DB
$input_password_hashed_for_check = hash("sha256", $decryption_password_input . $doc_data['encryption_password_salt']);

if (!hash_equals($doc_data['encryption_password_hash'], $input_password_hashed_for_check)) {
    // Penggunaan hash_equals() untuk perbandingan string hash yang aman (mencegah timing attack)
    $_SESSION['decrypt_error'] = "Password dekripsi (chiperkey) yang Anda masukkan salah.";
    catat_log_aktivitas(
        $mysqli, 
        $current_user_id, 
        'FILE_DECRYPT_FAIL', 
        'Percobaan dekripsi gagal untuk file "' . $doc_data['original_filename'] . '" (ID: ' . $doc_data['id'] . ') oleh pengguna "' . $current_username . '" (password salah).',
        $doc_data['id']
    );
    header('Location: dekripsi.php?id_error_doc=' . $doc_data['id']); // Kembalikan ke form dengan ID
    exit();
}

// --- Jika Password Benar, Lanjutkan Proses Dekripsi ---
$waktu_mulai_dekripsi = microtime(true);

// 1. Derive Kunci AES 128-bit dari password input dan KDF Salt yang tersimpan
$kdf_salt_bin_db = hex2bin($doc_data['kdf_salt']); // kdf_salt dari DB (hex) ke biner
$kdf_iterations_decrypt = 1000; // Harus sama persis dengan saat enkripsi
$derived_aes_key_bin_decrypt = hash_pbkdf2("sha256", $decryption_password_input, $kdf_salt_bin_db, $kdf_iterations_decrypt, 16, true);

// 2. Ambil IV dari database (disimpan sebagai hex, konversi ke biner)
$iv_hex_from_db = $doc_data['iv'];
$iv_bin_db = hex2bin($iv_hex_from_db);

// Pengecekan eksplisit panjang IV biner sebelum dekripsi
if ($iv_bin_db === false || strlen($iv_bin_db) !== AES128_CBC::AES_BLOCK_SIZE) { // AES_BLOCK_SIZE adalah 16
    $_SESSION['decrypt_error'] = "Proses dekripsi gagal: IV tidak valid (ID: " . $doc_data['id'] . "). IV Hex dari DB: " . htmlspecialchars($iv_hex_from_db) . ". Pastikan IV tersimpan dengan benar saat enkripsi.";
    catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DECRYPT_FAIL', 'Gagal dekripsi file "' . $doc_data['original_filename'] . '" (ID: ' . $doc_data['id'] . ') oleh pengguna "' . $current_username . '" karena IV tidak valid setelah konversi dari DB.', $doc_data['id']);
    header('Location: dekripsi.php?id_error_doc=' . $doc_data['id']);
    exit();
}

// Path ke file terenkripsi
$encrypted_file_path_absolute = __DIR__ . "/../" . $doc_data['encrypted_filepath']; 

if (!file_exists($encrypted_file_path_absolute) || !is_readable($encrypted_file_path_absolute)) {
    $_SESSION['decrypt_error'] = "File terenkripsi tidak ditemukan atau tidak bisa dibaca di server pada path: " . htmlspecialchars($doc_data['encrypted_filepath']);
    catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DECRYPT_FAIL', 'File terenkripsi "' . $doc_data['encrypted_filepath'] . '" tidak ditemukan/bisa dibaca untuk dokumen ID: ' . $doc_data['id'] . '.', $doc_data['id']);
    header('Location: dekripsi.php');
    exit();
}

// --- Proses Dekripsi File Sebenarnya ---
$decrypted_file_content = null;
try {
    $aes_decipher = new AES128_CBC($derived_aes_key_bin_decrypt);
    $encrypted_content_from_file = file_get_contents($encrypted_file_path_absolute);
    if ($encrypted_content_from_file === false) {
        throw new Exception("Gagal membaca konten file terenkripsi dari: " . $doc_data['encrypted_filepath']);
    }

    $decrypted_file_content = $aes_decipher->decrypt($encrypted_content_from_file, $iv_bin_db);

    if ($decrypted_file_content === false) {
        // Ini biasanya karena padding error, yang mengindikasikan kunci AES hasil derivasi salah atau IV salah atau data korup
        throw new Exception("Proses dekripsi gagal (kemungkinan kunci/IV salah, atau file korup - error padding).");
    }

} catch (Exception $e) {
    $_SESSION['decrypt_error'] = "Kesalahan selama proses dekripsi inti: " . $e->getMessage();
    catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DECRYPT_FAIL', 'Kesalahan inti saat dekripsi file "' . $doc_data['original_filename'] . '" (ID: ' . $doc_data['id'] . '): ' . $e->getMessage(), $doc_data['id']);
    header('Location: dekripsi.php?id_error_doc=' . $doc_data['id']);
    exit();
}

// --- Simpan File Hasil Dekripsi ---
// Menggunakan nama file asli, tambahkan timestamp + uniqid untuk menghindari konflik jika didekripsi ulang tanpa menghapus yg lama
$decrypted_filename_stored = time() . "_" . uniqid() . "_" . $doc_data['original_filename'];
$decrypted_file_save_dir_relative = "Hasil/hasildekripsi/"; // Relatif terhadap folder public/
$decrypted_file_save_path_relative = $decrypted_file_save_dir_relative . $decrypted_filename_stored;
$decrypted_file_save_dir_absolute = __DIR__ . "/../" . $decrypted_file_save_dir_relative; // Dari public/dashboard/ ke public/Hasil/hasildekripsi/
$decrypted_file_save_path_absolute = $decrypted_file_save_dir_absolute . $decrypted_filename_stored;

if (!is_dir($decrypted_file_save_dir_absolute)) {
    if (!mkdir($decrypted_file_save_dir_absolute, 0775, true)) {
        $_SESSION['decrypt_error'] = "Gagal membuat direktori penyimpanan file hasil dekripsi.";
        header('Location: dekripsi.php');
        exit();
    }
}

if (file_put_contents($decrypted_file_save_path_absolute, $decrypted_file_content) === false) {
    $_SESSION['decrypt_error'] = "Gagal menyimpan file hasil dekripsi ke server.";
    header('Location: dekripsi.php');
    exit();
}
$filesize_decrypted_bytes = strlen($decrypted_file_content);
$filesize_decrypted_kb = round($filesize_decrypted_bytes / 1024, 2);

// --- Update Metadata di Database `documents` ---
$decryption_timestamp = date('Y-m-d H:i:s');
$waktu_selesai_dekripsi = microtime(true);
$decryption_duration_seconds = round($waktu_selesai_dekripsi - $waktu_mulai_dekripsi, 2);

$sql_update_doc = "UPDATE documents SET 
                    file_status = 'Terdekripsi', 
                    decrypted_filename = ?, 
                    decrypted_filepath = ?, 
                    filesize_decrypted_kb = ?, 
                    decryption_duration_seconds = ?, 
                    decryption_timestamp = ?, 
                    decrypted_by_user_id = ?
                  WHERE id = ?";

$stmt_update_doc = $mysqli->prepare($sql_update_doc);
if ($stmt_update_doc) {
    $stmt_update_doc->bind_param("ssddisi",
        $decrypted_filename_stored,
        $decrypted_file_save_path_relative, // Simpan path relatif dari public/
        $filesize_decrypted_kb,
        $decryption_duration_seconds,
        $decryption_timestamp,
        $current_user_id,
        $document_id_to_decrypt
    );

    if ($stmt_update_doc->execute()) {
        $log_desc_decrypt_success = 'Pengguna "' . $current_username . '" berhasil mendekripsi file "' . $doc_data['original_filename'] . 
                                    '" (ID: ' . $doc_data['id'] . ') menjadi "' . $decrypted_filename_stored . '".';
        catat_log_aktivitas($mysqli, $current_user_id, 'FILE_DECRYPT_SUCCESS', $log_desc_decrypt_success, $doc_data['id']);

        // Base URL untuk link download, sama seperti di page_setup_header.php atau login.php
        $protocol_dl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host_dl = $_SERVER['HTTP_HOST'];
        $script_name_dl = $_SERVER['SCRIPT_NAME']; 
        $app_root_url_path_dl = '';
        $public_keyword_dl = '/public/';
        $public_pos_dl = strpos($script_name_dl, $public_keyword_dl);
        if ($public_pos_dl !== false) {
            $app_root_url_path_dl = substr($script_name_dl, 0, $public_pos_dl);
        } else {
            $path_parts_dl = explode('/', trim($script_name_dl, '/'));
            if (isset($path_parts_dl[0]) && $path_parts_dl[0] === 'AplikasiWebKripto') { 
                $app_root_url_path_dl = '/' . $path_parts_dl[0];
            }
        }
        $app_root_url_path_dl = rtrim($app_root_url_path_dl, '/');
        $base_app_url_for_download = $protocol_dl . $host_dl . $app_root_url_path_dl;
        
        $download_link_url = $base_app_url_for_download . '/public/' . $decrypted_file_save_path_relative;


        $_SESSION['decrypt_success'] = [
            'original_filename' => $doc_data['original_filename'],
            'decrypted_filename_stored' => $decrypted_filename_stored,
            'decrypted_filepath_relative' => $decrypted_file_save_path_relative, 
            'decrypted_size_kb' => $filesize_decrypted_kb,
            'duration' => $decryption_duration_seconds,
            'download_link' => $download_link_url 
        ];
        header('Location: dekripsi.php?id_decrypted=' . $document_id_to_decrypt);
        exit();

    } else {
        $_SESSION['decrypt_error'] = "Dekripsi file berhasil, TETAPI gagal memperbarui status di database: " . $stmt_update_doc->error;
        // Pertimbangkan menghapus file hasil dekripsi jika update DB gagal
        if(file_exists($decrypted_file_save_path_absolute)) unlink($decrypted_file_save_path_absolute);
    }
    $stmt_update_doc->close();
} else {
    $_SESSION['decrypt_error'] = "Dekripsi file berhasil, TETAPI gagal mempersiapkan statement update database: " . $mysqli->error;
    if(file_exists($decrypted_file_save_path_absolute)) unlink($decrypted_file_save_path_absolute);
}

header('Location: dekripsi.php'); // Fallback redirect jika ada error sebelum redirect sukses
exit();
?>