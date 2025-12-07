<?php
// AplikasiWebKripto/public/dashboard/enkripsi_proses.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

set_time_limit(0);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/aes128.php';
require_once __DIR__ . '/../../includes/log_helper.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $_SESSION['encrypt_error'] = "Sesi tidak valid atau Anda belum login.";
    header('Location: enkripsi.php');
    exit();
}

if ($_SESSION['role'] !== 'super_admin') {
    $_SESSION['encrypt_error'] = "Anda tidak memiliki izin untuk melakukan enkripsi.";
    header('Location: enkripsi.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['encrypt_submit_button'])) {
    $_SESSION['encrypt_error'] = "Akses tidak valid ke halaman proses.";
    header('Location: enkripsi.php');
    exit();
}

$encryption_password = $_POST['encryption_password'] ?? '';
$file_description = trim($_POST['file_description'] ?? '');
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];

if (empty($encryption_password) || strlen($encryption_password) < 3) {
    $_SESSION['encrypt_error'] = "Password enkripsi (chiperkey) harus diisi dan minimal 3 karakter.";
    header('Location: enkripsi.php');
    exit();
}

if (!isset($_FILES['file_to_encrypt']) || $_FILES['file_to_encrypt']['error'] !== UPLOAD_ERR_OK) {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => "File melebihi batas ukuran upload server (upload_max_filesize).",
        UPLOAD_ERR_FORM_SIZE  => "File melebihi batas ukuran yang ditentukan di form.",
        UPLOAD_ERR_PARTIAL    => "File hanya terupload sebagian.",
        UPLOAD_ERR_NO_FILE    => "Tidak ada file yang diunggah.",
        UPLOAD_ERR_NO_TMP_DIR => "Folder temporary untuk upload tidak ditemukan.",
        UPLOAD_ERR_CANT_WRITE => "Gagal menulis file ke disk.",
        UPLOAD_ERR_EXTENSION  => "Ekstensi PHP menghentikan proses upload file.",
    ];
    $error_code = $_FILES['file_to_encrypt']['error'] ?? UPLOAD_ERR_NO_FILE;
    $_SESSION['encrypt_error'] = $upload_errors[$error_code] ?? "Error upload tidak diketahui.";
    header('Location: enkripsi.php');
    exit();
}

$file_tmp_path    = $_FILES['file_to_encrypt']['tmp_name'];
$original_filename = basename($_FILES['file_to_encrypt']['name']);
$filesize_original_bytes = $_FILES['file_to_encrypt']['size'];
$filesize_original_kb  = round($filesize_original_bytes / 1024, 2);
$file_mime_type = mime_content_type($file_tmp_path); 

$file_info       = pathinfo($original_filename);
$file_extension  = strtolower($file_info['extension'] ?? '');
$filename_no_ext = $file_info['filename'];

$allowed_extensions = ["docx", "doc", "txt", "pdf", "xls", "xlsx", "ppt", "pptx", "jpg", "jpeg", "png", "gif", "mp3", "mp4", "mov", "mpg"];
$max_filesize_kb = 8192; // 8MB

if (!in_array($file_extension, $allowed_extensions)) {
    $_SESSION['encrypt_error'] = "Format file tidak diizinkan. Hanya: " . implode(", ", $allowed_extensions);
    header('Location: enkripsi.php');
    exit();
}
if ($filesize_original_kb > $max_filesize_kb) {
    $_SESSION['encrypt_error'] = "Ukuran file tidak boleh lebih besar dari " . ($max_filesize_kb / 1024) . "MB.";
    header('Location: enkripsi.php');
    exit();
}

$waktu_mulai_enkripsi = microtime(true);

$kdf_salt_bin = random_bytes(16); 
$kdf_salt_hex = bin2hex($kdf_salt_bin);
$kdf_iterations = 1000; 
$derived_aes_key_bin = hash_pbkdf2("sha256", $encryption_password, $kdf_salt_bin, $kdf_iterations, 16, true);

$encryption_password_salt_for_hash = bin2hex(random_bytes(16)); 
$encryption_password_db_hash = hash("sha256", $encryption_password . $encryption_password_salt_for_hash);

try {
    $iv_bin = random_bytes(16); 
    $iv_hex = bin2hex($iv_bin);
} catch (Exception $e) {
    $_SESSION['encrypt_error'] = "Gagal menghasilkan IV yang aman: " . $e->getMessage();
    header('Location: enkripsi.php');
    exit();
}

$encrypted_file_content = null;
try {
    $aes_cipher = new AES128_CBC($derived_aes_key_bin);
    $file_content_original = file_get_contents($file_tmp_path);
    if ($file_content_original === false) {
        throw new Exception("Gagal membaca konten file asli.");
    }
    $encrypted_file_content = $aes_cipher->encrypt($file_content_original, $iv_bin);
} catch (Exception $e) {
    $_SESSION['encrypt_error'] = "Terjadi kesalahan selama proses enkripsi: " . $e->getMessage();
    header('Location: enkripsi.php');
    exit();
}

$filename_no_ext_safe = preg_replace("/[^a-zA-Z0-9._-]/", "_", $filename_no_ext);
$encrypted_unique_filename = time() . "_" . uniqid() . "_" . $filename_no_ext_safe . "." . $file_extension . ".enc";
$encrypted_file_save_dir_relative = "Hasil/hasilenkripsi/"; 
$encrypted_file_save_path_relative = $encrypted_file_save_dir_relative . $encrypted_unique_filename;
$encrypted_file_save_dir_absolute = __DIR__ . "/../" . $encrypted_file_save_dir_relative;
$encrypted_file_save_path_absolute = $encrypted_file_save_dir_absolute . $encrypted_unique_filename;

if (!is_dir($encrypted_file_save_dir_absolute)) {
    if (!mkdir($encrypted_file_save_dir_absolute, 0775, true)) {
        $_SESSION['encrypt_error'] = "Gagal membuat direktori penyimpanan file terenkripsi.";
        header('Location: enkripsi.php');
        exit();
    }
}

if (file_put_contents($encrypted_file_save_path_absolute, $encrypted_file_content) === false) {
    $_SESSION['encrypt_error'] = "Gagal menyimpan file terenkripsi ke server.";
    header('Location: enkripsi.php');
    exit();
}
$filesize_encrypted_bytes = strlen($encrypted_file_content);
$filesize_encrypted_kb = round($filesize_encrypted_bytes / 1024, 2);

$encryption_timestamp = date('Y-m-d H:i:s');
$waktu_selesai_enkripsi = microtime(true);
$encryption_duration_seconds = round($waktu_selesai_enkripsi - $waktu_mulai_enkripsi, 2);

$sql_insert_doc = "INSERT INTO documents 
    (user_id, original_filename, mime_type, description, upload_timestamp, 
    encrypted_filename, encrypted_filepath, filesize_original_kb, filesize_encrypted_kb, 
    iv, kdf_salt, encryption_password_hash, encryption_password_salt, 
    encryption_duration_seconds, encryption_timestamp, file_status) 
    VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Terenkripsi')";

$stmt_insert_doc = $mysqli->prepare($sql_insert_doc);
if ($stmt_insert_doc) {
    // String tipe yang BENAR untuk 14 placeholder (upload_timestamp menggunakan NOW())
    // i = user_id
    // s = original_filename
    // s = mime_type
    // s = description
    // s = encrypted_filename
    // s = encrypted_filepath
    // d = filesize_original_kb
    // d = filesize_encrypted_kb
    // s = iv_hex
    // s = kdf_salt_hex
    // s = encryption_password_db_hash
    // s = encryption_password_salt_for_hash
    // d = encryption_duration_seconds
    // s = encryption_timestamp
    $bind_types = "isssssddssssds"; // Total 14 karakter

    $stmt_insert_doc->bind_param(
        $bind_types,
        $current_user_id,
        $original_filename,
        $file_mime_type,
        $file_description,
        $encrypted_unique_filename,
        $encrypted_file_save_path_relative,
        $filesize_original_kb,
        $filesize_encrypted_kb,
        $iv_hex,                            // Pastikan ini 32 karakter hex
        $kdf_salt_hex,                      // Pastikan ini 32 karakter hex
        $encryption_password_db_hash,
        $encryption_password_salt_for_hash, // Pastikan ini 32 karakter hex
        $encryption_duration_seconds,
        $encryption_timestamp
    );

    if ($stmt_insert_doc->execute()) {
        $new_document_id = $mysqli->insert_id;
        $log_description = 'Pengguna "' . $current_username . '" berhasil mengenkripsi file "' . $original_filename . 
                           '" menjadi "' . $encrypted_unique_filename . '".';
        catat_log_aktivitas($mysqli, $current_user_id, 'FILE_ENCRYPT', $log_description, $new_document_id);

        $_SESSION['encrypt_success'] = [
            'original_filename' => $original_filename,
            'encrypted_filename' => $encrypted_unique_filename,
            'original_size_kb' => $filesize_original_kb,
            'encrypted_size_kb' => $filesize_encrypted_kb,
            'duration' => $encryption_duration_seconds,
            'description' => $file_description
        ];
        header('Location: enkripsi.php');
        exit();
    } else {
        $_SESSION['encrypt_error'] = "Gagal menyimpan metadata file ke database: " . $stmt_insert_doc->error;
        if (file_exists($encrypted_file_save_path_absolute)) {
            unlink($encrypted_file_save_path_absolute);
        }
    }
    $stmt_insert_doc->close();
} else {
    $_SESSION['encrypt_error'] = "Gagal mempersiapkan statement database: " . $mysqli->error;
    // Cek apakah file sudah terlanjur dibuat jika statement awal gagal
    if (isset($encrypted_file_save_path_absolute) && file_exists($encrypted_file_save_path_absolute)) {
        unlink($encrypted_file_save_path_absolute);
    }
}

// Jika sampai sini berarti ada error sebelumnya
header('Location: enkripsi.php');
exit();
?>